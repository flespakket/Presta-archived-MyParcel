if (typeof Flespakket == 'undefined') { Flespakket = {}; }

var popup; // Handle to popup window
var consignments = {}; // Hyperlinks to consignments that haven't been processed yet
var locked = false; // Lock to prevent more than one consignment being created at a time

function onClickOnUnprocessedConsignment(event) {
    if (!popup || popup.closed) {
        // User closed the popup
        this.remove(); // Delete the link
    } else {
        popup.focus();
    }
}

Flespakket.PrestashopPlugin = {
    setConsignmentId: function(orderId, timestamp, consignmentId, tracktrace_link, retour){

        var flpa_div = document.createElement('div');

        // print checkbox
        var flpa_check = document.createElement('input');
        flpa_check.className = 'flpaleft flpacheck';
        flpa_check.type = 'checkbox';
        flpa_check.value = consignmentId;

        // pdf image
        var flpa_img = document.createElement('img');
        flpa_img.alt = 'print';
        flpa_img.src = '/modules/flespakket/images/flespakket_pdf.png';
        if(retour == 1) flpa_img.src = '/modules/flespakket/images/flespakket_retour.png';
        flpa_img.style.border = 0;

        // pdf image link
        var flpa_link = document.createElement('a');
        flpa_link.className = 'flespakket-pdf';
        flpa_link.onclick = new Function('return printConsignments(' + consignmentId + ');');
        flpa_link.href = '#';
        flpa_link.appendChild(flpa_img);

        // tracktrace link
        var flpa_track = document.createElement('a');
        flpa_track.target = '_blank';
        flpa_track.href = tracktrace_link;
        flpa_track.innerHTML = 'Track&Trace';

        // shove into DOM
        flpa_div.appendChild(flpa_check);
        flpa_div.appendChild(flpa_track);
        flpa_div.appendChild(flpa_link);
        var orderdiv = document.getElementById('flpa_exist_' + orderId);
        orderdiv.insertBefore(flpa_div, orderdiv.firstChild);

        popup.close();
        locked = false;
    }
};

var lastTimestamp = 0;
function _getTimestamp() {
    var ret = Math.round(new Date().getTime() / 1000);
    if (ret <= lastTimestamp) {
        ret = lastTimestamp + 1; // Make sure it is unique
    }
    return lastTimestamp = ret;
}

function createNewConsignment(orderId, retour)
{
    if (locked) {
        if (!popup || popup.closed) {
            // User closed the popup
        } else {
            popup.focus();
            return;
        }
    }

    // if empty, Flespakket will take the default package from the user
    var packageType = $('#flespakket_package_' + orderId).val();

    locked = true;
    var timestamp = _getTimestamp();

    var retourparam = '';
    if(retour == true) retourparam = '&retour=true';

    popup = window.open(
        '/modules/flespakket/process.php?action=post' + '&order_id=' + orderId + '&package=' + packageType + '&timestamp=' + timestamp + retourparam,
        'flespakket',
        'width=730,height=830,dependent,resizable,scrollbars'
    );

    if (window.focus) { popup.focus(); }
    return false;
}

function printConsignments(consignmentList)
{
    if (locked) {
        if (!popup || popup.closed) {
            // User closed the popup
        } else {
            popup.focus();
            return;
        }
    }
    locked = true;
    var timestamp = _getTimestamp();

    popup = window.open(
        '/modules/flespakket/process.php?action=print' + '&consignments=' + consignmentList + '&timestamp=' + timestamp,
        'flespakket',
        'width=415,height=365,dependent,resizable,scrollbars'
        );
    if (window.focus) { popup.focus(); }
    return false;
}

function printConsignmentSelection()
{
    var consignmentList = Array();
    var checkboxes = document.getElementsByClassName('flpacheck');
    for(var i = checkboxes.length - 1; i >= 0; i--)
    {
        if(checkboxes[i].checked == true && checkboxes[i].value != '')
        {
            consignmentList.push(checkboxes[i].value);
        }
    }
    return (consignmentList.length == 0) ? false : printConsignments(consignmentList.join('|'));
}

function processConsignmentSelection()
{
    var consignmentList = Array();
    var packageList = Array();
    var checkboxes = document.getElementsByClassName('flpacheck');

    for (var i = checkboxes.length - 1; i >= 0; i--) {
        if (checkboxes[i].checked == true) {
            consignmentList.push(checkboxes[i].id.replace('flpa_check_', ''));
            packageList.push($('#flespakket_package_' + checkboxes[i].id.replace('flpa_check_', '')).val());
        }
    }

    return (consignmentList.length > 0 && confirm("This will create " + consignmentList.length + " labels.\n\nAre you sure?"))
    ? processConsignments(consignmentList.join('|'), packageList.join('|'))
    : false;
}

function processConsignments(consignmentList, packageList)
{
    if (locked) {
        if (!popup || popup.closed) {
            // User closed the popup
        } else {
            popup.focus();
            return;
        }
    }
    locked = true;
    var timestamp = _getTimestamp();

    popup = window.open(
        '/modules/flespakket/process.php?action=process' + '&order_ids=' + consignmentList + '&package_list=' + packageList + '&timestamp=' + timestamp,
        'flespakket',
        'width=415,height=365,dependent,resizable,scrollbars'
        );
    if (window.focus) { popup.focus(); }
    return false;
}