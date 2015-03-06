$(function () {
    $("a.ajaxRemove").click( function (ev) {
        ev.preventDefault();
        var url = $(this).attr("href");
        var refresh = true;
        $.ajax({
            url: url,
            dataType: "json",
            error: function () {
                alert("Errore nella connessione, si prega di riprovare.");
            },
            success: function( data ) {
                var result = data.result;
                switch ( result ) {
                    case 'true':
                        alert('Voce eliminata correttamente.');
                        break;
                    case 'notDeactivated':
                        alert('Area tematica non disattivata.');
                        refresh = false;
                        break;
                    case 'operatorOnline':
                        alert('L\'operatore è online. Impossibile continuare.' );
                        refresh = false;
                        break;
                    case 'deskOpen':
                        alert('Lo sportello è aperto. Impossibile continuare.' );
                        refresh = false;
                        break;
                    default:
                        alert("Errore durante l'eliminazione.");
                        break;
                }
            },
            complete: function () {
                if ( refresh ) {
                    window.location.reload();
                }
            }
        });
    });
});