// Login check
$.ajax({
    url: "../rest/login",
    statusCode: {
        200: function() {
            // Get current user information
            $.getJSON( "../rest/api/user/whoami", function( user ) {
                Cookies.set('user',user);
                console.log(Cookies.get('user'));
                $("#header").load('components/header/header.html');
                $("#sidebar").load('components/sidebar/sidebar.html');
                $("#footer").load('components/footer/footer.html');
                
                // Manage routes
                routie({
                    'home': function() {
                        $("#main").load('components/main/'+user.type+'-home.html');
                    },
                    '*': function() {
                        $("#main").load('components/main/'+user.type+'-home.html');
                    }
                });
            });
        },
        401: function() {
            $("#main-wrapper").load('components/login/login.html');
        }
    }
});