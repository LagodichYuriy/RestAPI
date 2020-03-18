# Requirements

* PHP 7.1+
* MySQL/MariaDB


# Project Structure

* ./database.sql (database dump file) 
* ./config.php   (config file)
* ./src          (global classes directory)
* ./app          (website directory)
* ./app/src      (website-related files)


# Project Installation

1) Edit config.php
2) Import database (database.sql)
3) Add a line to hosts file: "127.0.0.1 api.challenge.localhost"
4) Configure RESTful URLs (nginx config sample file is listed below)

# Nginx config sample:

    server
    {
        listen 80;
        server_name api.challenge.localhost;
    
        deny all;
        allow 127.0.0.1;
    
    
        root /path/to/the/challenge/app;
        
        location = /favicon.ico
        {
            log_not_found off;
            access_log off;
        }
    
        location = /robots.txt
        {
            log_not_found off;
            access_log off;
        }
        
        # allow only index.php to be launched
        location = /index.php
        {
            # 404
            try_files $fastcgi_script_name =404;
            
            # default fastcgi_params
            include fastcgi_params;
            
            # fastcgi settings
            fastcgi_pass        php;
            fastcgi_index       index.php;
            fastcgi_buffers     8 16k;
            fastcgi_buffer_size 32k;
            
            # fastcgi params
            fastcgi_param DOCUMENT_ROOT $realpath_root;
            fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        }
        
        # prevent downloading/executing other php files
        location ~ \.php$
        {
            return 403;
        }
        
        location /
        {
            try_files $uri $uri/ /index.php?$args&_url=$uri;
        }
    }
    
# API

## HTTP Codes

* 200 - Success (OK).
* 400 - Bad Request (check your GET/POST params)
* 500 - Internal Server Error (you can't do anything)

## Error Handling

1) A valid response is a response with HTTP code 200 AND valid JSON structure AND an empty "errors" array
2) All other scenarios should be values as an error.

#### Error Response Sample

    {
        "errors":
        [
            "This employee does not exist"
        ],
    
        "result": [],
        "http_code": 400,
        "execution_time": "0.007",
        "memory_used": "2 MB"
    }


# Endpoints

## /Employees

#### GET

Required Fields: none.

Optional Fields:

* **limit** - positive integer, max value is 100. Default: 100.
* **page** - positive integer, implements pagination. Default: 1.  
* **order_field** - string, possible values are: "id", "first_name", "last_name", "email". Default: "id".
* **order_direction** - string, possible values are: "ASC", "DESC". Default: "ASC".

#### CURL Samples

    curl http://api.challenge.localhost/Employees
    
    
    
#### Response Sample
       
    {
        "result":
        [
            {
                "id": "1",
                "first_name": "Jon",
                "last_name": "Snow",
                "email": "jonsnow@challenge.com"
            },
            {
                "id": "2",
                "first_name": "Daenerys",
                "last_name": "Targaryen",
                "email": "daenerystargaryen@challenge.com"
            },
            {
                "id": "3",
                "first_name": "Mance",
                "last_name": "Rayder",
                "email": "mancerayder@challenge.com"
            },
            {
                "id": "4",
                "first_name": "Grey",
                "last_name": "Worm",
                "email": "greyworm@challenge.com"
            }
        ],
        
        "errors": [],
        "http_code": 200,
        "execution_time": "0.002",
        "memory_used": "2 MB"
    }


## /Reviews

#### GET

Required Fields: none.

Optional Fields:

* **employee_id** - positive integer, where provided shows reviews only for this employee. Default: none.
* **reviewer_id** - positive integer, where provided shows reviews only from this reviewer. Default: none.
* **limit** - positive integer, max value is 100. Default: 100.
* **page** - positive integer, implements pagination. Default: 1.  
* **order_field** - string, possible values are: "id", "first_name", "last_name", "email". Default: "id".
* **order_direction** - string, possible values are: "ASC", "DESC". Default: "ASC".

#### CURL Samples

    curl http://api.challenge.localhost/Reviews
    
    
    
#### Response Sample
       
    {
        "result":
        [
            {
                "id": "1",
                "rating": "2",
                "comment": null,
                "created": "2019-02-28 15:23:40",
                "employee_id": "3",
                "employee_first_name": "Mance",
                "employee_last_name": "Rayder",
                "employee_email": "mancerayder@challenge.com",
                "reviewer_id": "1",
                "reviewer_first_name": "Jon",
                "reviewer_last_name": "Snow",
                "reviewer_email": "jonsnow@challenge.com"
            },
            {
                "id": "2",
                "rating": "2",
                "comment": null,
                "created": "2020-02-28 15:33:02",
                "employee_id": "3",
                "employee_first_name": "Mance",
                "employee_last_name": "Rayder",
                "employee_email": "mancerayder@challenge.com",
                "reviewer_id": "2",
                "reviewer_first_name": "Daenerys",
                "reviewer_last_name": "Targaryen",
                "reviewer_email": "daenerystargaryen@challenge.com"
            },
            {
                "id": "3",
                "rating": "2",
                "comment": null,
                "created": "2020-02-28 15:34:48",
                "employee_id": "3",
                "employee_first_name": "Mance",
                "employee_last_name": "Rayder",
                "employee_email": "mancerayder@challenge.com",
                "reviewer_id": "1",
                "reviewer_first_name": "Jon",
                "reviewer_last_name": "Snow",
                "reviewer_email": "jonsnow@challenge.com"
            },
            {
                "id": "4",
                "rating": "2",
                "comment": "Comment!",
                "created": "2020-02-28 15:39:51",
                "employee_id": "4",
                "employee_first_name": "Grey",
                "employee_last_name": "Worm",
                "employee_email": "greyworm@challenge.com",
                "reviewer_id": "1",
                "reviewer_first_name": "Jon",
                "reviewer_last_name": "Snow",
                "reviewer_email": "jonsnow@challenge.com"
            }
        ],
        
        "errors": [],
        "http_code": 200,
        "execution_time": "0.002",
        "memory_used": "2 MB"
    }
    
#### POST

Required Fields:
* **employee_id** - positive integer
* **reviewer_id** - positive integer
* **rating** - positive integer, min value is 1, max value is 5

Optional Fields:

* **comment** - string. Default: none.

#### CURL Samples

    curl -d "reviewer_id=1&employee_id=4&rating=2&comment=Nice" -X POST http://api.challenge.localhost/Reviews
    
    
#### Response Sample

    {
        "result":
        {
            "review_id": "5"
        },
        
        "errors": [],
        "http_code": 200,
        "execution_time": "0.009",
        "memory_used": "2 MB"
    }
    