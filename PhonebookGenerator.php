<?php
    require './Settings.php';

    ## Constants
    # SettingsArray Keys stored in Constant so that one change needs to be made accross the whole file
    define("BRANDELEMENT", 'Brands');
    define("DIRECTORYELEMENT", 'Directories');
    define("PHONEBOOKDIRSELEMENT", 'Phonebooks');
    define("ACTIVEELEMENT", 'Active');
    define("FILENAMEELEMENT", 'FileName');
    define("MULTIVCARDELEMENT", 'MultiVCard');
    define("DATABASEELEMENT", 'Database');

    ## Variables
    $SettingsArray = [];

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Connect to DB & get list of Names & Extensions
    function ConnectToDB($DBServer, $Username, $Password, $Database)
    {
        $DBLink = mysqli_connect($DBServer, $Username, $Password) or die("Unable to connect to Database Server");
        mysqli_select_db($DBLink, $Database) or die("Unable to find specified database");
        
        $SQLCommand  =  "SELECT user, description 
                        FROM devices 
                        UNION ALL
                            SELECT custom_exten, description
                            FROM custom_extensions
                        UNION ALL
                            SELECT conference_room_id, display_name
                            FROM cxpanel_conference_rooms
                        ORDER BY description ASC";

        $Result = mysqli_query($DBLink, $SQLCommand) or die("No Data found in database");
        return mysqli_fetch_all($Result, MYSQLI_NUM);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    #region Misc Methods

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Writes content to a file
    function FileWriter($FilePath, $Content)
    {
        file_put_contents($FilePath, $Content);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Splits a Name String into Name & Surname, Returns an array
    function SplitName($FullName)
    {
        return explode(' ', $FullName, 2);
        
        // Name: Position 0
        // Surname: Position 1
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Checks of the loaded settings file is a blank template
    function IsConfigValid()
    {
        return ( LoadFromFile() != CreateBlankArray() );
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Creates a folder if one does not already exist
    function FolderCreator($Folder)
    {
        if ( !file_exists($Folder) )
        {
            mkdir($Folder, 0755, true);
        }

        return TRUE;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    #endregion

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    #region XML File Generators

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Generates XML Phonebook for Grandstream Devices
    function GrandStreamGenerator($ContactArray)
    {
        global $SettingsArray;
        
        $Writer = new XMLWriter();
        $Writer -> openMemory();
        $Writer -> setIndent(4);
        
        $Writer -> startDocument('1.0');
        $Writer -> startElement('AddressBook');
        
        foreach ($ContactArray as $Value)
        {
            $AllNames = SplitName($Value[1]);

            $Writer -> startElement('Contact');
            $Writer -> writeElement('LastName', @$AllNames[1]);
            $Writer -> writeElement('FirstName', @$AllNames[0]);
            $Writer -> startElement('Phone');
            $Writer -> writeElement('PhoneNumber', $Value[0]);
            $Writer -> writeElement('AccountIndex', 0);
            $Writer -> endElement(); // End Phone Element
            $Writer -> endElement(); // Contact Element
        }

        $Writer -> endElement(); // End AddressBook Element
        $Writer -> endDocument();
        
        $FilePath = $SettingsArray[DIRECTORYELEMENT][PHONEBOOKDIRSELEMENT] . $SettingsArray[BRANDELEMENT]["Grandstream"][FILENAMEELEMENT];
        FileWriter($FilePath, $Writer -> outputMemory(TRUE));

        $Writer -> flush();
        unset($Writer);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Generates XML Phonebook for Poly (Polycom) Devices
    function PolyGenerator($ContactArray)
    {
        global $SettingsArray;

        $Writer = new XMLWriter();
        $Writer -> openMemory();
        $Writer -> setIndent(4);
        
        $Writer -> startDocument('1.0');
        $Writer -> startElement('Directory');
        $Writer -> startElement('Item_List');
        
        foreach ($ContactArray as $Value)
        {
            $AllNames = SplitName($Value[1]);

            $Writer -> startElement('Item');
            $Writer -> writeElement('LN', @$AllNames[1]);
            $Writer -> writeElement('FN', @$AllNames[0]);
            $Writer -> writeElement('CT', $Value[0]);
            $Writer -> endElement(); // End Item Element
        }

        $Writer -> endElement(); // End Item_List Element
        $Writer -> endElement(); // End Directory Element
        $Writer -> endDocument();

        $FilePath = $SettingsArray[DIRECTORYELEMENT][PHONEBOOKDIRSELEMENT] . $SettingsArray[BRANDELEMENT]["Poly"][FILENAMEELEMENT];
        FileWriter($FilePath, $Writer -> outputMemory(TRUE));

        $Writer -> flush();
        unset($Writer);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Generates XML Phonebook for MicroSIP Clients
    function MicroSIPGenerator($ContactArray)
    {
        global $SettingsArray;

        $Writer = new XMLWriter();
        $Writer -> openMemory();
        $Writer -> setIndent(4);
        
        $Writer -> startDocument('1.0');
        $writer-> startElement('contacts');
        $writer-> writeAttribute('refresh', 0);
        
        foreach ($ContactArray as $Value) 
        {
            $writer->startElement('contact');
            $writer->writeAttribute('name', $contact['description']);
            $writer->writeAttribute('number', $contact['user']);
            $writer->writeAttribute('presence', 1);
            $writer->endElement();
        }

        $Writer -> endElement();
        $Writer -> endDocument();

        $FilePath = $SettingsArray[DIRECTORYELEMENT][PHONEBOOKDIRSELEMENT] . $SettingsArray[BRANDELEMENT]["MicroSIP"][FILENAMEELEMENT];
        FileWriter($FilePath, $Writer -> outputMemory(TRUE));

        $Writer -> flush();
        unset($Writer);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Generates XML Phonebook for Cisco Devices
    function CiscoGenerator($ContactArray)
    {
        global $SettingsArray;

        $Writer = new XMLWriter();
        $Writer -> openMemory();
        $Writer -> setIndent(4);
        
        $Writer -> startDocument('1.0');
        $Writer -> startElement('CiscoIPPhoneDirectory');
        
        $Writer -> writeElement('Title', "Phonebook");
        $Writer -> writeElement('Prompt', "Prompt Text");

        foreach ($ContactArray as $Value) 
        {
            $Writer -> startElement('DirectoryEntry');
            $Writer -> writeElement('Name', $Value[1]);
            $Writer -> writeElement('Telephone', $Value[0]);
            $Writer -> endElement(); // Close DirectoryEntry Tag
        }

        $Writer -> endElement(); // Close CiscoIPPhoneDirectory Tag
        $Writer -> endDocument();

        $FilePath = $SettingsArray[DIRECTORYELEMENT][PHONEBOOKDIRSELEMENT] . $SettingsArray[BRANDELEMENT]["Cisco"][FILENAMEELEMENT];
        FileWriter($FilePath, $Writer -> outputMemory(TRUE));

        $Writer -> flush();
        unset($Writer);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Generates XML Phonebook with elements specified by calling method
    function GenericPhonebookGenerator($ContactArray, $FileName, $PhoneBookElement, $ContactElement)
    {
        global $SettingsArray;

        $Writer = new XMLWriter();
        $Writer -> openMemory();
        $Writer -> setIndent(4);
        
        $Writer -> startDocument('1.0');
        $Writer -> startElement($PhoneBookElement);
        
        foreach ($ContactArray as $Value)
        {
            $Writer -> startElement($ContactElement);
            $Writer -> writeElement('Name', $Value[1]);
            $Writer -> writeElement('Ext', $Value[0]);
            $Writer -> endElement();
        }

        $Writer -> endElement();
        $Writer -> endDocument();

        $FilePath = $SettingsArray[DIRECTORYELEMENT][PHONEBOOKDIRSELEMENT] . $FileName;
        FileWriter($FilePath, $Writer -> outputMemory(TRUE));

        $Writer -> flush();
        unset($Writer);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    #endregion

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    #region vCard Stuffs

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    #region vCards Content Generators

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    ## vCard Constants
    define("BEGINVCARD", 'BEGIN:VCARD');
    define("NAMEANDSURNAME", 'FN:%1$s %2$s');
    define("ENDVCARD", 'END:VCARD');

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Generates Content for vCard v2.1
    function vCard2_1ContentGenerator($Name, $PhoneNumber)
    {
        $AllNames = SplitName($Name);

        $Content =
        BEGINVCARD . "\n" .
        'VERSION:2.1' . "\n" .
        'N:%2$s;%1$s;;' . "\n" .
        NAMEANDSURNAME . "\n" .
        'TEL;SIP;VOICE:%3$s' . "\n" .
        ENDVCARD;

        return sprintf($Content, $AllNames[0], @$AllNames[1], $PhoneNumber);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Generates Content for vCard v3
    function vCard3ContentGenerator($Name, $PhoneNumber)
    {
        $AllNames = SplitName($Name);

        $Content =
        BEGINVCARD . "\n" .
        'VERSION:3.0' . "\n" .
        'N:%2$s;%1$s;;;' . "\n" .
        NAMEANDSURNAME . "\n" .
        'TEL;TYPE=SIP,VOICE:%3$s' . "\n" .
        ENDVCARD;

        return sprintf($Content, $AllNames[0], @$AllNames[1], $PhoneNumber);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Generates Content for vCard v4
    function vCard4ContentGenerator($Name, $PhoneNumber)
    {
        $AllNames = SplitName($Name);

        $Content =
        BEGINVCARD . "\n" .
        'VERSION:4.0' . "\n" .
        'N:%2$s;%1$s;;;' . "\n" .
        NAMEANDSURNAME . "\n" .
        'TEL;TYPE=SIP,voice;VALUE=uri:tel:%3$s' . "\n" .
        ENDVCARD;

        return sprintf($Content, $AllNames[0], @$AllNames[1], $PhoneNumber);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    #endregion

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    #region Multi Entry vCard Generators & Version Selector

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Creates single vCard (v2.1) for all contacts
    function MultivCard2_1ContentGenerator($ContactArray)
    {
        $MasterContent = "";

        foreach ($ContactArray as $Value)
        {
            $MasterContent .= vCard2_1ContentGenerator($Value[1], $Value[0]);
            $MasterContent .= "\n";
        }

        return $MasterContent;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Creates single vCard (v3) for all contacts
    function MultivCard3ContentGenerator($ContactArray)
    {
        $MasterContent = "";

        foreach ($ContactArray as $Value) 
        {
            $MasterContent .= vCard3ContentGenerator($Value[1], $Value[0]);
            $MasterContent .= "\n";
        }

        return $MasterContent;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Creates single vCard (v4) for all contacts
    function MultivCard4ContentGenerator($ContactArray)
    {
        $MasterContent = "";
        
        foreach ($ContactArray as $Value) 
        {
            $MasterContent .= vCard4ContentGenerator($Value[1], $Value[0]);
            $MasterContent .= "\n";
        }

        return $MasterContent;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Selects correct vCard Version and calls the appropriate multiple entry (per file) content generator, then writes the content to file
    function MultiVCardVersionSelector($ContactArray, $vCardVersion)
    {
        global $SettingsArray;
        $MasterCard = "";
        
        switch ($vCardVersion)
        {
            case "v2_1":
                $MasterCard = MultivCard2_1ContentGenerator($ContactArray);
                break;
            case "v3":
                $MasterCard = MultivCard3ContentGenerator($ContactArray);
                break;
            case "v4":
                $MasterCard = MultivCard4ContentGenerator($ContactArray);
                break;
            default:
                echo "Invalid vCard Version Selected";
                break;
        }

        $Path = $SettingsArray[DIRECTORYELEMENT][PHONEBOOKDIRSELEMENT] . $SettingsArray[BRANDELEMENT][MULTIVCARDELEMENT][FILENAMEELEMENT];
        FileWriter($Path, $MasterCard);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    #endregion

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    #region Single Entry vCard Generators & Version Selector

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Creates individual vCards (v2.1) for each contact
    function SingleVCardv2_1Generator($ContactArray, $FilePath)
    {
        foreach ($ContactArray as $Value) 
        {
            $Path = $FilePath . $Value[1] . ".vcf";
            FileWriter($Path, vCard2_1ContentGenerator($Value[1], $Value[0]));
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Creates individual vCards (v3) for each contact
    function SingleVCardv3Generator($ContactArray, $FilePath)
    {
        foreach ($ContactArray as $Value) 
        {
            $Path = $FilePath . $Value[1] . ".vcf";
            FileWriter($Path, vCard3ContentGenerator($Value[1], $Value[0]));
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Creates individual vCards (v4) for each contact    
    function SingleVCardv4Generator($ContactArray, $FilePath)
    {
        foreach ($ContactArray as $Value) 
        {
            $Path = $FilePath . $Value[1] . ".vcf";
            FileWriter($Path, vCard4ContentGenerator($Value[1], $Value[0]));
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Selects correct vCard Version and calls the appropriate single entry (per file) content generator, which in turn will generate the files
    function SingleVCardVersionSelector($ContactArray, $vCardVersion)
    {
        global $SettingsArray;

        $FullfolderPath = $SettingsArray[DIRECTORYELEMENT][PHONEBOOKDIRSELEMENT] . $SettingsArray[DIRECTORYELEMENT]["SinglevCardSub"];
        
        if ( FolderCreator($FullfolderPath) )
        {
            switch ($vCardVersion)
            {
                case "v2_1":
                    SingleVCardv2_1Generator($ContactArray, $FullfolderPath);
                    break;
                case "v3":
                    SingleVCardv3Generator($ContactArray, $FullfolderPath);
                    break;
                case "v4":
                    SingleVCardv4Generator($ContactArray, $FullfolderPath);
                    break;
                default:
                    echo "Invalid vCard Version Selected";
                    break;
            }
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    #endregion

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    #endregion

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Execute Generator method for each requested brand in config file
    function GeneratorSelector($ContactArray)
    {
        global $SettingsArray;
        
        if ( FolderCreator($SettingsArray[DIRECTORYELEMENT][PHONEBOOKDIRSELEMENT]) )
        {
            # XML Methods

            if ($SettingsArray[BRANDELEMENT]["Grandstream"][ACTIVEELEMENT] == 1)
            {
                GrandStreamGenerator($ContactArray);
            }

            if ($SettingsArray[BRANDELEMENT]["Poly"][ACTIVEELEMENT] == 1)
            {
                PolyGenerator($ContactArray);
            }

            if ($SettingsArray[BRANDELEMENT]["MicroSIP"][ACTIVEELEMENT] == 1)
            {
                MicroSIPGenerator($ContactArray);
            }

            if ($SettingsArray[BRANDELEMENT]["Cisco"][ACTIVEELEMENT] == 1)
            {
                CiscoGenerator($ContactArray);
            }

            if ($SettingsArray[BRANDELEMENT]["Yealink"][ACTIVEELEMENT] == 1)
            {
                GenericPhonebookGenerator($ContactArray, $SettingsArray[BRANDELEMENT]["Yealink"][FILENAMEELEMENT], "YeastarIPPhoneDirectory", "DirectoryEntry");
            }

            if ($SettingsArray[BRANDELEMENT]["Web"][ACTIVEELEMENT] == 1)
            {
                GenericPhonebookGenerator($ContactArray, $SettingsArray[BRANDELEMENT]["Web"][FILENAMEELEMENT], "Phonebook", "Contact");
            }

            ## vCard Methods

            if ($SettingsArray[BRANDELEMENT][MULTIVCARDELEMENT][ACTIVEELEMENT] == 1)
            {
                MultiVCardVersionSelector($ContactArray, $SettingsArray[BRANDELEMENT][MULTIVCARDELEMENT]["Version"]);
            }

            if ($SettingsArray[BRANDELEMENT]["SingleVCard"][ACTIVEELEMENT] == 1)
            {
                SingleVCardVersionSelector($ContactArray, $SettingsArray[BRANDELEMENT]["SingleVCard"]["Version"]);
            }
        }   
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Main function - Everything is started / controlled from here
    function ControlMethod()
    {
        global $SettingsArray;

        if ( IsConfigValid() )
        {
            $SettingsArray = LoadFromFile();

            $ContactList = ConnectToDB($SettingsArray[DATABASEELEMENT]["Host"], $SettingsArray[DATABASEELEMENT]["Username"], $SettingsArray[DATABASEELEMENT]["Password"], $SettingsArray[DATABASEELEMENT][DATABASEELEMENT]); // Populate Contact Array from DB   

            GeneratorSelector($ContactList);
        } else 
        {
            echo "Blank Settings.json found";
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    ControlMethod(); // Start Script Execution

?>