<?php

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

# Constants
define("ACTIVEELEMENT", 'Active');
define("LOGOELEMENT", 'Logo');
define("FILENAMEELEMENT", 'FileName');

# Variables
$SettingsFile = "Settings.json";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function CreateBlankArray()
{
    return array(
        "Database" => array(
            "Host" => "",
            "Username" => "",
            "Password" => "",
            "Database" => ""
        ),
        "Brands" => array(
            "Grandstream" => array(
                ACTIVEELEMENT => 1,
                LOGOELEMENT => "Grandstream.png",
                FILENAMEELEMENT => "GS_Phonebook.xml"
            ),
            "Poly" => array(
                ACTIVEELEMENT => 1,
                LOGOELEMENT => "Poly.png",
                FILENAMEELEMENT => "Poly_Phonebook.xml"
            ),
            "MicroSIP" => array(
                ACTIVEELEMENT => 1,
                LOGOELEMENT => "MicroSIP.png",
                FILENAMEELEMENT => "MS_Phonebook.xml"
            ),
            "Cisco" => array(
                ACTIVEELEMENT => 0,
                LOGOELEMENT => "Cisco.png",
                FILENAMEELEMENT => "Cisco_Phonebook.xml"
            ),
            "Yealink" => array(
                ACTIVEELEMENT => 0,
                LOGOELEMENT => "Yealink.png",
                FILENAMEELEMENT => "Yealink_Phonebook.xml"
            ),
            "Web" => array(
                ACTIVEELEMENT => 1,
                LOGOELEMENT => "Web.png",
                FILENAMEELEMENT => "Web_Phonebook.xml"
            ),
            "SingleVCard" => array(
                ACTIVEELEMENT => 0,
                LOGOELEMENT => "vCard.png",
                "Version" => "v2_1"
            ),
            "MultiVCard" => array(
                ACTIVEELEMENT => 0,
                LOGOELEMENT => "vCard.png",
                FILENAMEELEMENT => "Contacts.vcf",
                "Version" => "v2_1"
            )
        ),
        "Directories" => array(
            "Phonebooks" => "PhonebookFiles/",
            "SinglevCardSub" => "SinglevCards",
            "Images" => "Images/"
        ),
        "PBX" => array(
            "URL" => "",
            LOGOELEMENT => ""
        ),
        "Hidden" => array(
            "Blank" => 0,
            "WebRTC" => 0
        )
    );
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Saves array to Settings JSON file
function SaveToFile($Array)
{
    global $SettingsFile;
    file_put_contents($SettingsFile, json_encode($Array) );
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Loads Settings JSON file and returns an array, Creates one from template if file is missing then returns blank file
function LoadFromFile()
{
    global $SettingsFile;

    if ( file_exists($SettingsFile) )
    {
        return json_decode( file_get_contents($SettingsFile), TRUE);
    } else
    {
        SaveToFile( CreateBlankArray() );
        return LoadFromFile();
    }
    
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

?>