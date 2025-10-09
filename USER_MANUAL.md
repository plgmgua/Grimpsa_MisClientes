# Odoo Contacts Component - User Manual

**Version:** 1.0.1-STABLE  
**Component:** com_odoocontacts  
**For:** Joomla 5.0+

---

## Table of Contents

1. [Installation](#installation)
2. [Configuration](#configuration)
3. [User Interface Overview](#user-interface-overview)
4. [Managing Contacts](#managing-contacts)
5. [Work Orders (OT)](#work-orders-ot)
6. [Troubleshooting](#troubleshooting)
7. [FAQ](#faq)

---

## Installation

### Step 1: Download the Component
Download the installation package: `com_odoocontacts_v1.0.1.zip`

### Step 2: Install via Joomla Administrator
1. Log in to your Joomla Administrator panel
2. Navigate to **System → Install → Extensions**
3. Click **Browse** and select `com_odoocontacts_v1.0.1.zip`
4. Click **Upload & Install**
5. Wait for the success message

### Step 3: Verify Installation
1. Go to **Components → Odoo Contacts** in the admin menu
2. You should see the Dashboard page with version v1.0.1

---

## Configuration

### Accessing Component Settings

1. Go to **Components → Odoo Contacts**
2. Click the **Options** button (top right)
3. Configure the following settings:

### Odoo Connection Settings

| Setting | Description | Default Value |
|---------|-------------|---------------|
| **Odoo URL** | The XML-RPC endpoint URL | `https://grupoimpre.odoo.com/xmlrpc/2/object` |
| **Database Name** | Your Odoo database name | `grupoimpre` |
| **Username** | Odoo user email/username | `admin@grimpsa.com` |
| **API Key** | Your Odoo API key | (provided by admin) |
| **Contacts Per Page** | Number of contacts to display per page | `20` |
| **Enable Debug Mode** | Show debug information in console | `No` |
| **Work Order Destination URL** | URL where work order data is sent | `https://grimpsa_webserver.grantsolutions.cc/index.php/orden-de-trabajo` |

### Save Configuration
Click **Save & Close** to apply your settings.

---

## User Interface Overview

### Main Contact List

When you access the component on the frontend, you'll see:

#### Header Section
- **Title**: "Mis Clientes" (My Clients)
- **Agent Name**: Your sales agent name displayed on the right
- **Search Box**: Search contacts by name, email, or phone
- **Create Button**: Add new client contact

#### Contact Table

| Column | Description |
|--------|-------------|
| **Cliente** | Client name |
| **NIT** | Tax ID/VAT number |
| **Teléfono** | Phone/Mobile number |
| **Email** | Email address |
| **Ciudad** | City |
| **Acciones** | Action buttons (Edit, Delete, Quotation, OT) |

#### Action Buttons

- **📝 Edit**: Modify client information
- **🗑️ Delete**: Remove client (with confirmation)
- **💰 Cotización**: Create quotation (redirects to quotation system)
- **🚚 OT**: Create work order

#### Pagination
- **Items per page**: Select 15, 30, or 100
- **Navigation**: Previous/Next buttons
- **Page indicator**: Shows current page and total

---

## Managing Contacts

### Viewing Contacts

1. **Access the component** from your Joomla menu
2. You'll see only **your** contacts (filtered by sales agent)
3. **Parent contacts only** are displayed in the main list
4. Child contacts (delivery addresses, contact persons) are hidden from list

### Creating a New Contact

1. Click the **"+ Crear Cliente"** button
2. Fill in the contact form:
   - **Name** (required)
   - **Email**
   - **Phone**
   - **Mobile**
   - **VAT/NIT**
   - **Address**
   - **City**
   - **Type**: Select contact type

3. Click **Save** to create the contact
4. The contact will be saved to Odoo and appear in your list

### Editing a Contact

1. Click the **Edit** (pencil icon) button on any contact row
2. Modify the fields you want to change
3. Click **Save** to update
4. Changes are immediately synced to Odoo

### Deleting a Contact

1. Click the **Delete** (trash icon) button
2. A confirmation modal appears
3. Confirm the deletion
4. The contact is removed from Odoo

> ⚠️ **Warning**: Deletion is permanent and cannot be undone!

### Searching Contacts

1. Enter search term in the search box
2. Press **Enter** or click the **Search** button
3. Results filter automatically
4. Click **Clear** to reset search

Search works across:
- Contact name
- Email address
- Phone numbers
- Mobile numbers

---

## Work Orders (OT)

The Work Order feature allows you to create delivery orders with delivery address and contact information.

### Starting a Work Order

1. Find the client in your contact list
2. Click the **OT** (truck icon) button
3. A modal window opens with a two-step wizard

### Step 1: Delivery Information

#### Client Information (Auto-filled)
- **Client Name**: Automatically populated
- **NIT**: Tax ID displayed

#### Delivery Address Options

**Option A: Select Existing Address**
1. Use the dropdown menu
2. Select a previously saved delivery address
3. Address and city are displayed in preview

**Option B: Enter New Address**
1. Fill in the manual input fields:
   - **Nombre de Dirección**: Descriptive name (e.g., "Bodega Central")
   - **Dirección**: Street address
   - **Ciudad**: City name

2. Check **"Agregar dirección a cliente"** if you want to save it
3. Click **"Guardar Dirección"** button (appears when checkbox is checked)
4. Wait for success notification
5. New address appears in dropdown and is auto-selected

#### Delivery Instructions
- Enter any special delivery instructions (optional)
- Examples: "Deliver after 2 PM", "Call before delivery", etc.

#### Navigation
Click **"Siguiente"** (Next) to proceed to Step 2

### Step 2: Contact Person

#### Contact Person Options

**Option A: Select Existing Contact**
1. Use the dropdown menu
2. Options include:
   - **Parent Contact**: "Contacto Principal - [Name]"
   - **Additional Contacts**: Previously saved contact persons

**Option B: Enter New Contact**
1. Fill in manual fields:
   - **Nombre**: Contact person's name
   - **Teléfono**: Contact person's phone

2. Check **"Agregar contacto a cliente"** to save
3. Click **"Guardar Contacto"** button
4. Wait for success notification
5. New contact appears in dropdown and is auto-selected

#### Summary Section
Review your selections:
- **Dirección de Entrega**: Delivery address
- **Instrucciones**: Delivery instructions

#### Finalizing Work Order
1. Review all information
2. Click **"Crear Orden de Trabajo"** (Create Work Order)
3. You'll be redirected to the work order system with all data

### Work Order Data Sent

The following information is sent to the destination URL:
- `client_id`: Client Odoo ID
- `contact_name`: Client name
- `contact_vat`: NIT/Tax ID
- `x_studio_agente_de_ventas`: Sales agent name
- `delivery_address`: Combined address and city
- `instrucciones_entrega`: Delivery instructions
- `contact_person_name`: Contact person name
- `contact_person_phone`: Contact phone number

---

## Troubleshooting

### Problem: No Contacts Showing

**Possible Causes:**
- Incorrect sales agent name mapping
- Odoo connection issues
- No contacts assigned to your agent

**Solutions:**
1. Check your Joomla user name matches Odoo agent field
2. Verify Odoo connection in component settings
3. Contact administrator to check agent field in Odoo

### Problem: Search Not Working

**Solutions:**
1. Clear your browser cache
2. Try searching with different terms
3. Check if contacts exist with those search terms
4. Enable Debug Mode to see console errors

### Problem: Cannot Create Work Order

**Symptoms:**
- Modal doesn't open
- "Siguiente" button doesn't work
- Save buttons don't appear

**Solutions:**
1. **Check if address is selected or filled**: You must either select an existing address OR fill all manual fields
2. **Check if contact is selected or filled**: Same for contact person
3. **Clear browser cache and reload page**
4. **Enable Debug Mode**: Check console for errors

### Problem: Configuration Page Shows Error

**Error:** "Form::loadForm could not load file"

**Solution:**
- This was fixed in v1.0.1
- Make sure you're using the latest version
- Reinstall the component if needed

### Problem: Child Contacts Showing in List

**Solution:**
- This was fixed in v1.0.1
- Child contacts (delivery addresses, contact persons) are now filtered out
- Only parent contacts appear in main list
- Reinstall v1.0.1 if you still see child contacts

---

## FAQ

### Q: How do I enable debug mode?
**A:** Go to Components → Odoo Contacts → Options → Enable Debug Mode → Yes → Save

### Q: What does debug mode do?
**A:** It shows console messages for troubleshooting, displays component version on page load, and logs API interactions.

### Q: Can I change the number of contacts per page?
**A:** Yes, use the dropdown at the bottom of the contact list to select 15, 30, or 100 items per page.

### Q: Where are my child contacts?
**A:** Child contacts (delivery addresses, contact persons) are hidden from the main list. They're only visible when:
- Creating a work order (OT)
- Viewing parent contact details

### Q: Can I delete multiple contacts at once?
**A:** No, currently you must delete contacts one at a time.

### Q: What happens if I don't save a new address/contact?
**A:** If you don't check the "Agregar dirección/contacto a cliente" checkbox, the information is used only for that work order and not saved to Odoo.

### Q: Can I edit a work order after creation?
**A:** No, once created, work orders are sent to the destination system. Edit them there.

### Q: What if my delivery address has no child contacts?
**A:** You can use the manual input fields to create a new delivery address on-the-fly while creating a work order.

### Q: Why can't I see other agents' contacts?
**A:** Contacts are filtered by sales agent. You can only see contacts assigned to you in Odoo.

### Q: How do I change my sales agent name?
**A:** Your sales agent name comes from your Joomla user name. Contact your administrator to change it.

### Q: Can I export contacts to Excel?
**A:** This feature is not currently available. It's planned for a future release.

---

## Support

For technical support, please contact:
- **Email**: admin@grimpsa.com
- **Website**: https://grimpsa.com

For Odoo-specific issues:
- Contact your Odoo administrator
- Check Odoo connection settings in component configuration

---

## Version History

### v1.0.1-STABLE (Current)
- ✅ Parent-only contact list filtering
- ✅ Two-step work order wizard
- ✅ Manual address/contact input with save option
- ✅ Debug mode toggle
- ✅ Configuration UI with proper labels
- ✅ Version badge on dashboard
- ✅ Async contact/address creation

### v1.0.0-STABLE
- Initial release
- Basic contact management
- Odoo integration
- Search and pagination

---

## Tips & Best Practices

1. **Keep contact information updated**: Regular updates ensure accurate work orders
2. **Use descriptive delivery address names**: e.g., "Bodega Principal", "Oficina Zona Norte"
3. **Add delivery instructions**: Help delivery team with specific requirements
4. **Save frequently used addresses**: Check "Agregar dirección" for recurring delivery points
5. **Search efficiently**: Use partial names or phone numbers for faster results
6. **Disable debug mode in production**: Only enable when troubleshooting issues

---

*This manual is for version 1.0.1-STABLE. For the latest updates, check the component dashboard in your Joomla admin panel.*

