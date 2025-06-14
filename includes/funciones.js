function filtro(str, pagina, gett)
{
    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function()
    {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
        {
            document.getElementById("tablafiltrada").innerHTML = xmlhttp.responseText;
        }
    }
    var q;
    q = "&q=" + str;

//alert( pagina + "?" + gett + q);
    xmlhttp.open("GET", pagina + "?" + gett + q  , true);
    xmlhttp.send();
}
