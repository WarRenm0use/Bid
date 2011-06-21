function conecta() {
    FB.login(function(response) {
        if (response.session) {
            if (response.perms) {
                window.location = "?sec=log&do=reg";
            } else {
                // user is logged in, but did not grant any permissions
//                alert("ok maso");
            }
        } else {
            // user is not logged in
//            alert("fail");
        }
    }, {
        perms:'email,publish_stream,offline_access'
    });
}

function handleSessionResponse(response) {
    // if we dont have a session, just hide the user info
    if (!response.session) {
        alert("no logeado");
        return;
    }
}