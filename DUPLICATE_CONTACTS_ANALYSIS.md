# Duplicate Contacts Analysis

## Potential Issues Found

### 1. **No Deduplication by Contact ID**
**Location:** `OdooHelper::parseContactsFromAllResults()`

**Issue:** The method adds contacts to the array without checking if a contact with the same ID already exists.

**Code:**
```php
$contacts[] = $normalizedContact;  // Line 360 - No duplicate check
```

**Problem:** If Odoo returns the same contact multiple times (which can happen with complex queries or relationships), it will be added multiple times to the results.

**Fix:** Add deduplication by contact ID before adding to array.

---

### 2. **Parent ID Detection Logic May Be Flawed**
**Location:** `OdooHelper::parseContactsFromAllResults()` lines 288-331

**Issue:** The `$hasParentId` flag is set in multiple places with different logic:
- Line 300-305: Checks if `parent_id` field exists as array
- Line 328-331: Checks if `parent_id` is boolean (this seems incorrect)

**Problem:** If `parent_id` detection fails, child contacts might be included when they shouldn't be, or parent contacts might be filtered out incorrectly.

**Fix:** Consolidate parent_id detection logic and ensure it's reliable.

---

### 3. **No Deduplication in ContactsModel**
**Location:** `ContactsModel::getItems()` lines 70-110

**Issue:** The model processes contacts from the helper but doesn't deduplicate them before returning.

**Code:**
```php
foreach ($contacts as $contact) {
    // ... normalization ...
    $validContacts[] = $normalizedContact;  // No duplicate check
}
```

**Problem:** Even if duplicates are introduced during normalization, they won't be filtered out.

---

### 4. **Odoo Query Returns All Contacts Without Domain Filter**
**Location:** `OdooHelper::getContactsByAgent()` line 131

**Issue:** The Odoo query uses an empty domain filter:
```xml
<value><array><data/></array></value> <!-- Args - EMPTY! -->
```

**Problem:** This returns ALL contacts from Odoo, then filters by agent name in PHP. If Odoo has duplicate records or the same contact appears in multiple relationships, all will be returned.

**Fix:** Add domain filter to Odoo query to filter by agent at the database level.

---

## Recommended Fixes

### Fix 1: Add Deduplication by ID in parseContactsFromAllResults()

```php
private function parseContactsFromAllResults($result, $agentName)
{
    // ... existing code ...
    
    $contacts = [];
    $seenIds = []; // Track seen contact IDs
    
    foreach ($values as $value) {
        // ... existing parsing code ...
        
        if (isset($contact['x_studio_agente_de_ventas']) && 
            $contact['x_studio_agente_de_ventas'] === $agentName && 
            !$hasParentId) {
            
            $contactId = isset($contact['id']) ? $contact['id'] : '0';
            
            // Skip if we've already seen this contact ID
            if (isset($seenIds[$contactId])) {
                continue;
            }
            $seenIds[$contactId] = true;
            
            // ... rest of normalization ...
            $contacts[] = $normalizedContact;
        }
    }
    
    return $contacts;
}
```

### Fix 2: Add Deduplication in ContactsModel

```php
public function getItems()
{
    // ... existing code ...
    
    $validContacts = [];
    $seenIds = []; // Track seen contact IDs
    
    foreach ($contacts as $contact) {
        if (is_array($contact)) {
            $contactId = isset($contact['id']) ? (string)$contact['id'] : '0';
            
            // Skip duplicates
            if (isset($seenIds[$contactId])) {
                continue;
            }
            $seenIds[$contactId] = true;
            
            // ... rest of normalization ...
            $validContacts[] = $normalizedContact;
        }
    }
    
    return $validContacts;
}
```

### Fix 3: Fix Parent ID Detection

```php
// In parseContactsFromAllResults()
$hasParentId = false;
$parentIdValue = '';

foreach ($value['struct']['member'] as $member) {
    $fieldName = $member['name'];
    
    if ($fieldName === 'parent_id') {
        // Check if parent_id has a value (not false/empty)
        if (isset($member['value']['array']['data']['value'])) {
            $hasParentId = true;
            $parentIdValue = isset($member['value']['array']['data']['value'][0]['int']) 
                ? (string)$member['value']['array']['data']['value'][0]['int'] 
                : '';
        } elseif (isset($member['value']['boolean']) && $member['value']['boolean'] === true) {
            // This case seems wrong - parent_id shouldn't be boolean
            // But if Odoo returns it this way, handle it
            $hasParentId = true;
        }
        // If parent_id is false or empty, hasParentId remains false
    }
    
    // ... rest of field processing ...
}
```

### Fix 4: Add Domain Filter to Odoo Query (Optional but Recommended)

Instead of returning all contacts and filtering in PHP, filter at the Odoo level:

```php
// In getContactsByAgent()
$domain = [
    ['x_studio_agente_de_ventas', '=', $agentName],
    ['parent_id', '=', false]  // Only parent contacts
];

$domainXml = $this->buildDomainXml($domain);

// Use domain in query instead of empty array
<param>
   <value><array><data>' . $domainXml . '</data></array></value>
</param>
```

---

## Summary

The main issue is **lack of deduplication by contact ID**. Contacts can appear multiple times if:
1. Odoo returns duplicates in the response
2. The same contact is processed multiple times
3. Parent/child relationships cause the same contact to appear in different contexts

**Priority fixes:**
1. ✅ Add ID-based deduplication in `parseContactsFromAllResults()`
2. ✅ Add ID-based deduplication in `ContactsModel::getItems()`
3. ✅ Fix parent_id detection logic
4. ⚠️ Consider adding domain filter to Odoo query (more complex, but more efficient)

