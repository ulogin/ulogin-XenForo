/** @param {jQuery} $ jQuery Object */
!function ($, window, document, _undefined) {
    XenForo.uLoginSync = function ($data) {

        base = $("base").attr("href");
        var uloginNetwork = $('#ulogin_synchronisation').find('.ulogin_network');

        uloginNetwork.click(function (e) {
            e.preventDefault();
            var network = $(this).attr('data-ulogin-network');
            var identity = $(this).attr('data-ulogin-identity');
            uloginDeleteAccount(network,identity);
        });

        function uloginDeleteAccount(network,identity){
            jQuery.ajax({
                url: base +'/?ulogin',
                type: 'POST',
                dataType: 'json',
                data: {
                    network: network,
                    identity: identity
                },
                error: function (data, textStatus, errorThrown) {
                    alert('Не удалось выполнить запрос');
                },
                success: function (data) {
                    switch (data.answerType) {
                        case 'error':
                            alert(data.title + "\n" + data.msg);
                            break;
                        case 'ok':
                            console.log(data.identity);
                            var accounts = $('#ulogin_accounts'),
                                nw = accounts.find('[data-ulogin-network='+network+']');
                            if (nw.length > 0) nw.hide();
                            break;
                        default:
                            break;
                    }
                }
            });
        }
    };

    // *********************************************************************

    XenForo.register('.ulogin_network', 'XenForo.uLoginSync');
}
(jQuery, this, document);