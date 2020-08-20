# FreePBX-Web-Phonebook
This project was created as a way to get a web browsable phonebook for my company's PBX
It is still rough around the edges. It was a side project and I wasn't able to finish it.

The primary purpose of this project was to be a browser based phonebook to easily display all of the extensions on the PBX and allow the user to search them by name or extension.

It's secondary purpose was to be a central application which can generate the XML phonebooks for the physical phones.

---

## Installation

Copy the files into a subfolder in the root web folder
eg: /var/www/html/phonebook

The files can be downloaded and placed into this folder manually or downloaded into a sub folder with GIT

---

## Configuration

Rename the settings.json.example file to settings.json and customize it to your environment
The settings file layout is explained below.

### Database
* Host: Set this to the Hostname / IP of the PBX database server
* Username: User account for the FreePBX database, this is usually "freepbxuser"
* Password: Password for the FreePBX database
* Database: this is the database name of the freepbx database, this is usually asterisk

### Brands
* Brand: This is the name of the brand
  * Active: This is a 1 or 0 to determine whether a phonebook should be created for this brand
  * Logo: This is a relative link to the brand logo
  * FileName: This is the filename you would like the phonebook for this brand to be named

### Directories
* Phonebooks: This is the relative path to the directory where the XML phonebooks will be saved
* SinglevCardSub: this is the subfolder in the phonebooks directory where the single vCards will be saved
* Images: This is the relative path to the folder that contains the images for the site, eg: brand logos

### PBX (Not Yet Implemented)
This section is supposed to be used to create a button on the web phonebook header to easily navigate to the PBX admin portal
* URL: This is the URL of the admin portal of the PBX

> The URL field can be changed by editing the first link in the index.php file on line 68-ish

* Logo: This is a relative link to the logo of the PBX

### Hidden (Not Yet Implemented)
> On my company's PBX, I rename unused extensions to [Blank] so that i can find them easier and not have to constantly delete & re-create them.
> I also noticed the using the web phone in UCP will create a 2<sup>nd</sup> extension for your user.
> I included this section to exclude these records from the web phonebook

This section contains a snippet of text which should exclude the entry from results and a 0 or 1 to determine whether it should be ignored or not.

eg: "WebRTC": 1
This will exclude all records containing "WebRTC" in the name