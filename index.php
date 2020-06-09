
<!-- include jquery and socket IO -->

<!-- <script src="js/jquery.js"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.js" integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc=" crossorigin="anonymous"></script>
<!-- <script src="js/socket.io.js"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.2.0/socket.io.js" integrity="sha256-yr4fRk/GU1ehYJPAs8P4JlTgu0Hdsp4ZKrx8bDEDC3I=" crossorigin="anonymous"></script>

<!-- creating form to enter usernmae -->

<!-- Enter your name -->
<form onsubmit="return enterName()">
  <input id="name" placeholder="Enter name">
  <input type="submit">
</form>

<!-- user from past chat -->
<ul id="users"></ul>

<!-- sending message to someone new -->
<form onsubmit="return messageToNewUser()">
  <input id="newUserName" placeholder="Enter user name">
  <input type="submit">
</form>
 


<!-- Send message -->
<form onsubmit="return sendMessage()">
  <input id="message" placeholder="Enter message">
  <input type="submit">
</form>


<ul id="messages"></ul>

<script>
  // creating io instance
  var io = io("http://localhost:3000");
 
  var receiver = "";
  var sender = "";


  function enterName(){
    // get user name
    var name = document.getElementById("name").value;
    // console.log(name);
    // send it to server
    io.emit("user_connected", name);

    // save sender name
    sender = name;
    var user_type = ""
    // fetching user form past and their chats
    $.ajax({
      url : "http://localhost:3000/get_user",
      method: "POST",
      data: {username:name},
      success: function(response){
        //console.log("response: ",response);
        var contact = JSON.parse(response);
        // console.log("from php file: ", contact);
        // console.log(contact[contact.length-1]['user_type']);

        var users = "";

        if(contact[contact.length-1]['user_type'] === 'receiver'){
          // console.log("yeah");
          for (var a = 0; a < contact.length-1; a++){
            users += "<li><button onclick='onUserSelected(this.innerHTML);'>" + contact[a].receiver + "</button></li>";
          }
        } else{
          for (var a = 0; a < contact.length-1; a++){
            users += "<li><button onclick='onUserSelected(this.innerHTML);'>" + contact[a].sender + "</button></li>";
          }
          console.log("fish");
        }

      // appending to list above
      document.getElementById("users").innerHTML += users;
      }
    });

    // prevent the form from submitting
    return false;
    }

    // listen from server
    io.on("user_connected", function(username){
      //console.log("from php",username);

      var users = "";
      users += "<li><button onclick='onUserSelected(this.innerHTML);'>" + username + "</button></li>"; 

      // // appending to list above
      document.getElementById("users").innerHTML += users;
    });

    function onUserSelected(username){
      // console.log(username);
      receiver = username;

      // calling ajax to load messages
      $.ajax({
        url: "http://localhost:3000/get_messages",
        method: "POST",
        data:{
          sender: sender,
          receiver: receiver
        },
        success: function(response){
          //console.log(response);

          var messages = JSON.parse(response);
          var html = "";

          for (var a = 0; a < messages.length; a++){
            html += "<li>"+ messages[a].sender +" says: "+ messages[a].message +"</li>";
          }

          //append in list
          document.getElementById("messages").innerHTML = html;

        }
      });
    }
    
    function sendMessage(){
      //get message
      var message = document.getElementById("message").value;

      // send message to server
      io.emit("send_message", {
        sender: sender,
        receiver: receiver,
        message: message
      });

      var html = "";
      html += "<li>You said:" + message +"</li>";
      document.getElementById("messages").innerHTML += html;
      return false;
    }

    //listen from server for message
    io.on("new_message", function(data){
      // console.log(data);

      //display message in list
      var html = "";
      html += "<li>"+ data.sender +" says: "+ data.message +"</li>";

      // appending the messages
      document.getElementById("messages").innerHTML += html;
    });

    function messageToNewUser(){
      var newUser = document.getElementById("newUserName").value;
      var message = document.getElementById("message").value;
      var sender = document.getElementById("name").value;

      // console.log(newUser);
      // console.log(sender);
      // console.log(message);

      // receiver = newuser;
      io.emit("message_new_user", {
        sender : sender,
        receiver : newUser,
        message : message
      });

      var html = "";
      html += "<li>You said:" + message +"</li>";
      document.getElementById("messages").innerHTML += html;
      return false;
      
    }

</script>
