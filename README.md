# joomla-profile-extension

Profile extension plugin adds a new tab with two fields to Joomla users.  

Instructions

* Use Joomla install option and load IndraSoft-Extend-Profile.zip.
* search for 'User - Organisation' and open it.
* You can make each field manadatory, optional or disable by selecting the corresponding option for each field.
* Enable the plugin and save it.
* If you open an existing user or create a new user you should see the 'User - Organisation' tab.

By default only en-GB and en-US locales are supported.

Technical

The joomla user profile DB table is used to store field keys and values. All field keys start with 'organization'.
The code is really simple and can be changed with ease as long as correct strings are used.

To Do

Expand the technical part so it is clear which files or code should be changed to support new fields, change field names or add new locales.
