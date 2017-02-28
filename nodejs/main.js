var http = require('http');
var url = require('url');

var server = http.createServer(function(req, res) {
    var page = url.parse(req.url).pathname;
    console.log(page);
    res.writeHead(200, {"Content-Type": "text/html"});
    res.render('index.html');
    if(page == "/test") {
        action(res);
    }
    res.end();
});

var action = function(res) {
    var retour = {
        test: "test"
    };
    res.write(JSON.stringify(retour));
}
server.listen(8081);
