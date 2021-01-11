<!DOCTYPE html>
<html lang="en">
    <head>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
        <link rel="stylesheet" href="Style.css">
        <title>WebPhonebook</title>

        <script>
            /* When the user clicks on the button, toggle between hiding and showing the dropdown content */
            function dropDownFunction()
            {
                document.getElementById("dropDownList").classList.toggle("show");
            }

            // Close the dropdown menu if the user clicks outside of it
            window.onclick = function(event)
            {
                if (!event.target.matches('.dropbtn'))
                {
                    var dropdowns = document.getElementsByClassName("dropdown-content");
                    var i;
                    for (i = 0; i < dropdowns.length; i++)
                    {
                        var openDropdown = dropdowns[i];
                        if (openDropdown.classList.contains('show'))
                        {
                            openDropdown.classList.remove('show');
                        }
                    }
                }
            }

            function showResult(str)
            {
                if (str.length==0)
                {
                    document.getElementById("livesearch").innerHTML="";
                    document.getElementById("livesearch").style.border="0px";
                    return;
                }
            
                if (window.XMLHttpRequest)
                {
                    // code for IE7+, Firefox, Chrome, Opera, Safari
                    xmlhttp=new XMLHttpRequest();
                } else
                {
                    // code for IE6, IE5
                    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
                }
                
                xmlhttp.onreadystatechange=function()
                {
                    if (this.readyState == 4 && this.status == 200)
                    {
                        document.getElementById("livesearch").innerHTML=this.responseText;
                        document.getElementById("livesearch").style.border="1px solid #A5ACB2";
                    }
                }
                
                xmlhttp.open("GET", "LiveSearch.php?q=" + str, true);
                xmlhttp.send();
            }
        </script>

        <div class="d-flex bg-dark">
            <div class="align-self-center text-white">
                <a class="nav-link" href="https://PBX-Server/admin/"><img src="Images/Logo.png" height=30px alt="FreePBX Admin" /></a>
            </div>
            <div class="align-self-center text-white flex-grow-1">
            </div>
            <div class="align-self-center text-white ">
                <a class="nav-link" href="PhonebookFiles/Web_Phonebook.xml">Web</a>
            </div>
            <div class="align-self-center text-white ">
                <a class="nav-link" href="PhonebookFiles/GS_Phonebook.xml"><img src="Images/GrandStream.png" height=30px alt="Grandstream" /></a>
            </div>
            <div class="align-self-center text-white ">
                <a class="nav-link" href="PhonebookFiles/Poly_Phonebook.xml"><img src="Images/Poly.png" height=30px alt="Poly" /></a>
            </div>
            <div class="align-self-center text-white ">
                <a class="nav-link" href="PhonebookFiles/MS_Phonebook.xml"><img src="Images/MicroSIP.png" height=30px alt="MicroSIP" /></a>
            </div>
            <div class="align-self-center text-white ">
                <a class="nav-link" href="#"><img src="Images/Cisco.png" height=30px alt="Cisco" /></a>
            </div>
            <div class="align-self-center text-white ">
                <a class="nav-link" href="#"><img src="Images/Yealink.png" height=30px alt="Yealink" /></a>
            </div>
            <div class="align-self-center text-white ">
                <a class="nav-link" href="#">Single vCard</a>
            </div>
            <div class="align-self-center text-white ">
                <a class="nav-link" href="#">Multi vCards</a>
            </div>
            <div class="align-self-center text-white flex-grow-1">
            </div>
            <div class="align-self-center text-white mt-3 mr-3">
                <form>
                    <input class="form-control" type="text" size="22" onkeyup="showResult(this.value)" placeholder="Name / Extension">
                    <div id="livesearch" class="bg-dark text-white"></div>
                </form>
            </div>
        </div>

    </head>
    
    <body>
        <?php
            include 'Web_Phonebook.php'
        ?>
    </body>
    
    <footer>
    </footer>
</html>
