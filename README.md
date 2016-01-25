# codeigniter-json-api
One-file plugin for converting CodeIgniter to JSON API

# Usage
The idea is simple: you can call your models directly from API as URI parameters. For example, if you have a method named "get_post($id_post)" inside a CI model named "blog", you can call it via
http://myapp/index.php/api/model/blog/get_post/{:id_post}

Of course, you can rename function "model()" to "index()" in order to shorten the URI. 
Don't forget to add your authentication system to "__construct()", so your API is fully secured. 

# Installing
1. Copy "api.php" into application/controllers
2. Enjoy!
