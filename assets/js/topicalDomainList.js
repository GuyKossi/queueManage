$(function () {
    $('input.activateCheckbox').click( function (e) {
        e.preventDefault();
        var url = $(this).val();
        var checkbox = $(this);
        $.ajax({
            url: url,
            dataType: "json",
            error: function () {
                alert("Errore nel processare la richiesta, si prega di riprovare.");
            },
            success: function( data ) {
                var result = data.result;
                if ( result === "true" ) {
                    var verb = checkbox.prop("checked") ? "disattivata" : "attivata";
                    alert("Area tematica " + verb + " correttamente.");
                    checkbox.prop("checked", !checkbox.prop("checked"));
                } else if ( result === "ticketInQueue" ) {
                    alert( "Impossibile disattivare l'area tematica perché sono presenti ticket in coda." );
                } else {
                    alert("Errore nel processare la richiesta, si prega di riprovare.");
                }
            }
        });
    });
    
    $('a.tdEditLink').click( function (e) {
        var checked = $(this).parent().prev().find('input[type=checkbox]').prop('checked');
        if ( checked ) {
            alert('Errore: è necessario disattivare l\'area tematica');
            e.preventDefault();
        }
    });
});