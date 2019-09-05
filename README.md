## The Prestashop plugin is archived, look at the following link for the latest version of the MyParcel plugin. https://github.com/myparcelnl


#Flespakket Prestashop plugin                         
--------------------------------------------
------  install guide  version 1.1.1  ------
--------------------------------------------

Tested on Prestashop 1.5.4.1 and 1.5.5.0 and 1.6.0.5

To install the module, follow these steps:
1) Copy the module files:
   /modules
   /override
   to the root of your Prestashop installation
2) Go to admin panel and in the "Advanced Parameters" submenu choose "Performance" item. In the "Smarty" section set "Force compilation" for "Template cache" setting and disable "Cache" setting. Save the page
3) In the admin panel open the "Modules" section and go to the "Shipping and Logistics" category
4) Find the Flespakket module in the list and click "Install" button
5) Open orders overview page and make sure that the new columns with the Flespakket functionality presented in the grid
6) Open "Performance" settings page again and set "Template cache" setting on "Never recompile template files" option and enable "Cache" setting. Save the page

Congratulations! The module is installed and ready to use. Go to the orders overview page and use the Flespakket functionality inside Prestashop.

NOTE: if your installation has succeeded, but nothing is visible:
assuming you tried to modify the cache settings and still no luck, you should manually remove the following file:
/cache/class_index.php
Prestashop will create it automatically upon reload, this time adding the classes we added in the Flespakket module.
