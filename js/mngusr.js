var auth2; // The Sign-In object.
var googleUser; // The current user.

/**
 * Calls startAuth after Sign in V2 finishes setting up.
 */
var appStart = function() {
  gapi.load('auth2', initSigninV2);
};

/**
 * Initializes Signin v2 and sets up listeners.
 */
var initSigninV2 = function() {
  auth2 = gapi.auth2.init({
      client_id: '1072089522959-lncmb7n5llcqm2sjoei28ufm6g63fatm.apps.googleusercontent.com',
      // scope: 'profile'
  });

  options = new gapi.auth2.SigninOptionsBuilder();  
  // options.setFetchBasicProfile(true);
  options.setPrompt('consent');
  options.setScope('profile').setScope('email');  
  // element = document.getElementById('gsignin');
  gapi.signin2.render('gsignin', options);
  // auth2.attachClickHandler(element, options, onSuccess, onFailure);
  
  // Listen for sign-in state changes.
  auth2.isSignedIn.listen(signinChanged);

  // Listen for changes to current user.
  auth2.currentUser.listen(userChanged);

  // Sign in the user if they are currently signed in.
  if (auth2.isSignedIn.get() == true) {
    auth2.signIn(options);
  }

  // Start with the current live values.
  refreshValues();
};

/**
 * Listener method for sign-out live value.
 *
 * @param {boolean} val the updated signed out state.
 */
var signinChanged = function (val) {
  console.log('Signin state changed to ', val);
  //document.getElementById('signed-in-cell').innerText = val;
};

/**
 * Listener method for when the user changes.
 *
 * @param {GoogleUser} user the updated user.
 */
var userChanged = function (user) {
  console.log('User now: ', user);
  googleUser = user;
  updateGoogleUser();
  //document.getElementById('curr-user-cell').innerText =
   // JSON.stringify(user, undefined, 2);
};

/**
 * Updates the properties in the Google User table using the current user.
 */
var updateGoogleUser = function () {
	if(googleUser){
	if (auth2.isSignedIn.get() == true){
	  signWithServer();
//          $("#sendMessage").load("sendMessageForm.html");
        editableGrid = new EditableGrid("DemoGridJSON"); 
        editableGrid.tableLoaded = function() { this.renderGrid("RSVPList", "testgrid"); };
        editableGrid.loadJSON("rsvp.json");
      }
	// document.getElementById("content").innerHTML='<object type="text/html" data="?php echo file_get_contents("sendMessage.php"); ?" ></object>';
	//console.log('displaying php');
    /*document.getElementById('user-id').innerText = googleUser.getId();
    document.getElementById('user-scopes').innerText =
      googleUser.getGrantedScopes();
    document.getElementById('auth-response').innerText =
      JSON.stringify(googleUser.getAuthResponse(), undefined, 2);*/
  } else {

    /*document.getElementById('user-id').innerText = '--';
    document.getElementById('user-scopes').innerText = '--';
    document.getElementById('auth-response').innerText = '--';*/
	
  }
};

/**
 * Retrieves the current user and signed in states from the GoogleAuth
 * object.
 */
var refreshValues = function() {
  if (auth2){
    console.log('Refreshing values...');

    googleUser = auth2.currentUser.get();

    // document.getElementById('curr-user-cell').innerText =
      // JSON.stringify(googleUser, undefined, 2);
    // document.getElementById('signed-in-cell').innerText =
      // auth2.isSignedIn.get();

    updateGoogleUser();
	//signWithServer();
  }
}


var onSuccess = function(googleUser) {
  console.log('in onSuccess');
	// var id_token = googleUser.getAuthResponse().id_token;
	// var xhr = new XMLHttpRequest();
	// xhr.open('POST', 'signinuser.php');
	// xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	// xhr.onload = function() {
	  // console.log('Signed in as: ' + xhr.responseText);
	  // alert(xhr.responseText);
	// };
	// xhr.send('idtoken=' + id_token);  
	// event.preventDefault();   
	// document.getElementById("content").innerHTML='<object type="text/html" data="?php echo file_get_contents("home.php"); ?" ></object>';
	// console.log('displaying php');
}
var onFailure = function(error) {
    console.log(error);
};
function signOut() {  
//FIXME: change to signOut();  
    auth2.disconnect().then(function () {
      console.log('User signed out.');	  
    });
}
  
function signWithServer() {
	var id_token = googleUser.getAuthResponse(true).id_token;
        var access_token = googleUser.getAuthResponse(true).access_token;
//	console.log('id_token= '+ id_token);
	var xhr = new XMLHttpRequest();
	xhr.open('POST', 'signinuser.php');
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xhr.onload = function() {
	  console.log(xhr.responseText);
          //document.write(xhr.responseText);
	};
	xhr.send('idtoken=' + id_token
                + '&accesstoken=' + access_token);  
	// event.preventDefault();   
	// document.getElementById("content").innerHTML='<object type="text/html" data="?php echo file_get_contents("home.php"); ?" ></object>';
	// console.log('displaying php');
}  
  
  
  