<!DOCTYPE html>
<html lang="en">

    <?php
        ## Constants
        define("REGULARELEMENT", "Regular");

        // Global Variables
        $ArrayArray = array();
        $ImportArray = [];

        //Settings Variables
        $PhonebookPath = 'PhonebookFiles\Web_Phonebook.xml';
        
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // Loads phonebook.xml into array
        function LoadInfo($XMLPath)
        {
            $XMLFile = file_get_contents($XMLPath);
            $ContactArrayXML = simplexml_load_string($XMLFile, "SimpleXMLElement", LIBXML_NOCDATA);
            $ContactArrayJSON = json_encode($ContactArrayXML);
            return json_decode($ContactArrayJSON, TRUE);
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // Displays array in neatly formatted table
        function DisplayTable($Array)
        {
            $TableHeading ="";
            $TableData ="";

            $TableHeading = '
                <table class="table-striped w-100 text-white">
                    <tr>
                        <th class="text-left">Name</th>
                        <th class="text-right">Extension</th>
                    </tr>
                ';

            for ($i = 0; $i < sizeof($Array); $i++)
            {
                $TableData = $TableData . "
                    <tr>
                        <td class='text-left'>
                            <a href='tel:" . $Array[$i]['Ext'] . "'>" . $Array[$i]['Name'] . "</a>
                        </td>
                        <td class='text-right'>
                            <a href='tel:" . $Array[$i]['Ext'] . "'>" . $Array[$i]['Ext'] . "</a>
                        </td>
                    </tr>";
            }

            echo $TableHeading . $TableData . "</table>";
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // Creates an array key that accepts an arrray for value IF one does not alreay exist
        function CreateMasterArrayValue($Tag)
        {
            global $ArrayArray;

            if (!array_key_exists($Tag, $ArrayArray))
            {
                $ArrayArray[$Tag] = array();
            }
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // Sorts contacts by tag into their corresponding arrays in the master array
        function AutoTagger($Array)
        {
            global $ArrayArray;         
            $ContactElement = "Contact";
            $NameElement = "Name";
            $ExtElement = "Ext";

            for ($i = 0; $i < sizeof($Array[$ContactElement]); $i++)
            {
                if (preg_match("/([\(\[]\w+[\)\]])\ ?(.*)/m", $Array[$ContactElement][$i][$NameElement], $matches)) // If name contains a tag
                {
                    $TagName =  str_replace(array('(', ')', '[', ']'), '', $matches[1]);
                    CreateMasterArrayValue($TagName);

                    array_push($ArrayArray[$TagName], array('Name'=>$matches[2], 'Ext'=>$Array[$ContactElement][$i][$ExtElement]));
                } else
                {
                    CreateMasterArrayValue(REGULARELEMENT);
                    array_push($ArrayArray[REGULARELEMENT], array('Name'=>$Array[$ContactElement][$i][$NameElement], 'Ext'=>$Array[$ContactElement][$i][$ExtElement]));
                }
            }
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // Script Starts Here
        $ImportArray = LoadInfo($PhonebookPath);
        AutoTagger($ImportArray);
    ?>

    <head>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
        <link rel="stylesheet" href="Style.css">
        <title>WebPhonebook Main Content</title>
    </head>
    <body>
        <div class="d-flex m-1">
            <div class="p-2 m-1 bg-dark text-white flex-grow-1">
                <h2>Contacts</h2>
                <?php DisplayTable($ArrayArray[REGULARELEMENT]) ?>
            </div>
            <div class="p-2 m-1 bg-dark text-dark">
                <?php
                    foreach ($ArrayArray as $Key => $Value) 
                    {
                        if ($Key != REGULARELEMENT)
                        {
                            echo '
                              <div class="p-1 m-1 mb-2 text-white">
                                <h2>' . $Key . '</h2>';
                                DisplayTable($Value);
                            echo '</div>';
                        }
                    }
                ?>
            </div>
        </div>
    </body>
</html>