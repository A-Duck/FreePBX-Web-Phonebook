<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
<?php
  $xmlDoc = new DOMDocument();
  $xmlDoc -> load("PhonebookFiles/Web_Phonebook.xml");
  $x = $xmlDoc -> getElementsByTagName('Contact');

  //get the q parameter from URL
  $q = $_GET["q"];

  //lookup all links from the xml file if length of q > 0
  if (strlen($q) > 0)
  {
    $ResultItem="";
    $Output = '
      <table class="table-striped w-100 text-white">
        <tr>
          <th>Name</th>
          <th>Extension</th>
        </tr>';

    for($i=0; $i < ($x -> length); $i++)
    {
      $NameNode = $x -> item($i) -> getElementsByTagName('Name');
      $ExtNode = $x -> item($i) -> getElementsByTagName('Ext');
      
      if ($NameNode -> item(0) -> nodeType == 1)
      {
        //find a link matching the search text
        if ( (stristr($NameNode -> item(0) -> childNodes -> item(0) -> nodeValue, $q) ) || (stristr($ExtNode -> item(0) -> childNodes -> item(0) -> nodeValue, $q) ) )
        {
          if ($ResultItem == "")
          {
            $ResultItem = "
              <tr>
                <td class='text-left'>
                  <a href='tel:" . $ExtNode -> item(0) -> childNodes -> item(0) -> nodeValue . "'>" . $NameNode -> item(0) -> childNodes -> item(0) -> nodeValue . "</a>
                </td>
                <td class='text-right'>
                  <a href='tel:" . $ExtNode -> item(0) -> childNodes -> item(0) -> nodeValue . "'>" . $ExtNode -> item(0) -> childNodes -> item(0) -> nodeValue . "</a>
                </td>
              </tr>";
          } else
          {
            $ResultItem = $ResultItem . "
            <tr>
              <td class='text-left'>
                <a href='tel:" . $ExtNode -> item(0) -> childNodes -> item(0) -> nodeValue . "'>" . $NameNode -> item(0) -> childNodes -> item(0) -> nodeValue . "</a>
              </td>
              <td class='text-right'>
                <a href='tel:" . $ExtNode -> item(0) -> childNodes -> item(0) -> nodeValue . "'>" . $ExtNode -> item(0) -> childNodes -> item(0) -> nodeValue . "</a>
              </td>
            </tr>";
          }
        }
      }
    }
    
    $Output = $Output . $ResultItem . "</table>";
  }

  // Set output to "no suggestion" if no hint was found
  // or to the correct values
  if ($ResultItem == "")
  {
    $Response = "No Results";
  } else
  {
    $Response = $Output;
  }

  //output the response
  echo $Response;
?> 