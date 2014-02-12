PrestaCenter XML Export Free
================================================================================

All paths are relative to the PrestaShop root directory,
So "/modules " means "[path to your Prestashop]/modules".

Installing the module:

* In the administration module select "Add New Module" and select the file
  prestacenterxmlexportfreeXXX.zip from your computer.
* Select from the group "Other modules" module "PrestaCenter XML Export Free"
  and click on "Install" .
* In the main BO (administration) menu module is the last item on the menu
  "Catalogue"

--------------------------------------------------------------------------------
The module requires some writable directories (chmod 755 or chmod 775).
Should it be necessary to manually set the access rights , in terms of these
directory:
/override/classes
/modules/prestacenterxmlexportfree/templates
/xml
(directory /xml is necessary to create, if not created during installation)

Along with the module are automatically installed XML feed templates for
services Google Merchant, heureka.cz, zbozi.cz, pricemania.sk and "General"
service for public use. For each feed can be added to any number of options 
which will have the same structure of the feed, but varies with the chosen 
language and currency in which they will add the names and prices of products.
Individual variations must have different name of the destination file.

It is possible to define own XML feed template for other price comparison 
services. Description of XML templates will be displayed in the form for 
feed editing (See help).

All XML feeds will be generated in the directory /xml - so that the URL will
look like this: 
http://www.myshop.com/xml/google-en-eur.xml

If your hosting sent XML file with the wrong MIME header, you can
URL feed can also use the form:
http://www.myshop.com/xml/getfeed.php?file=google-en-eur.xml
File getfeed.php sends the correct HTTP header (Content-Type: application/xml)
and the time when the feed was last modified.
