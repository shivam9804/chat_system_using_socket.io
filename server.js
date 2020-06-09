//creating express instace
var express = require("express");
var app = express();

//creating http instance
var http = require("http").createServer(app);

// creating socket io instance
var io = require("socket.io")(http);

// creating body parser instance
var bodyParser = require("body-parser");

// enable URL encoded for POST requests
app.use(bodyParser.urlencoded({
    extended: true
}));

// creating mysql instance
var mysql = require("mysql");

// connecting with database
var connection = mysql.createConnection({
    "host" : "localhost",
    "user" : "root",
    "password" : "1234",
    "database" : "b2b_updated_with_admin"
})

// connect
connection.connect(function(error){
    // show error if any
});

// enabling headers required for POST request
app.use(function (request, result, next){
    result.setHeader("Access-Control-Allow-Origin", "*");
    next();
});

app.post("/get_user", function(request, result){
    var contactLength = '';
    connection.query("SELECT DISTINCT receiver FROM chat WHERE sender = '"+ request.body.username +"'", function(error, contact){
        contactLength = contact.length;
        if(contactLength == 0){
            connection.query("SELECT DISTINCT sender FROM chat WHERE receiver = '"+ request.body.username +"'", function(error, contact1){
                contact1[contact1.length] = {"user_type":"sender"};
                // console.log("from js file",contact1)
                result.send(JSON.stringify(contact1));
            });
        }else{
            contact[contact.length] = {"user_type":"receiver"};
            console.log("from js file else",contact);
            result.send(JSON.stringify(contact));
        }
    });
    
})

// creating api to fetch all messages
app.post("/get_messages", function(request, result){
    // get all messages from database
    connection.query("SELECT * FROM chat WHERE (sender = '"+ request.body.sender +"' AND receiver = '" + request.body.receiver + "') OR (sender = '" + request.body.receiver + "' AND receiver = '" + request.body.sender + "')", function(error,messages){
        // response will be JSON
        //console.log(messages);
        result.send(JSON.stringify(messages));
    });
});


var users = [];

io.on("connection", function(socket){
    console.log("user connected", socket.id);

    //attaching incoming listener for new user
    socket.on("user_connected", function(username){
        // console.log(username);
        // save in array
        // assigning socketid to users
        users[username] = socket.id;
        //console.log(users[username] = socket.id);

        //notify to current user the contacts
        //io.emit("user_connected", username);

    });

    // listen form client
    socket.on("send_message", function(data){
        // console.log(data);

        // send data to receiver
        // get the scoket id of receiver
        var socketId = users[data.receiver];
        
        // send message to receiver 
        io.to(socketId).emit("new_message", data);

        // saving message in database
        connection.query("INSERT INTO chat (sender, receiver, message) VALUES ('"+ data.sender +"','"+ data.receiver +"','"+ data.message +"')", function(error, result){

        });
    });

    // listen from client for new user
    socket.on("message_new_user", function(data){
        // console.log("sender: "+ data.sender +" receiver: "+ data.receiver +" message: "+data.message);
        // storing message in database for user to fetch when he logs in 
        connection.query("INSERT INTO chat (sender, receiver, message) VALUES ('"+ data.sender +"','"+ data.receiver +"','"+ data.message +"')", function(error, result){

        });
    });

});

//  starting the server
http.listen(3000, function(){
    console.log("Server up and running");
});

