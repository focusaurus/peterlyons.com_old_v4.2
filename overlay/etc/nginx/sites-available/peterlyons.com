server {
    listen 80 default;
    server_name peterlyons.com www.peterlyons.com;
    access_log /var/log/nginx/peterlyons.com.access.log;
    error_log /var/log/nginx/peterlyons.com.error.log;

    location / {
        root /var/www/peterlyons.com;
        index index.php index.html;
        # this serves static files that exist without running other rewrite tests
        if (-f $request_filename) {
            expires 30d;
            #break;
        }
        # this sends all non-existing file or directory requests to index.php
        if (!-e $request_filename) {
            rewrite ^/persblog(.+)$ /persblog/index.php?q=$1 last;
            rewrite ^/problog(.+)$ /problog/index.php?q=$1 last;
        }
    }
    
    # deny access to .htaccess files, if Apache's document root
    # concurs with nginx's one
    location ~ /\.ht {
        deny all;
    }

    location /app/ {
        rewrite /app(.*) $1 break;
        proxy_pass http://localhost:9100;
    }
    
    #error_page 404 /404.html;

    # redirect server error pages to the static page /50x.html
    #
    #error_page 500 502 503 504 /50x.html;
    #location = /50x.html {
    #    root /var/www/nginx-default;
    #}

    # pass the PHP scripts to FastCGI server
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9200;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME /var/www/peterlyons.com$fastcgi_script_name;  # same path as above
        fastcgi_param QUERY_STRING $query_string;
        fastcgi_param REQUEST_METHOD $request_method;
        fastcgi_param CONTENT_TYPE $content_type;
        fastcgi_param CONTENT_LENGTH $content_length;

        fastcgi_param SCRIPT_NAME $fastcgi_script_name;
        fastcgi_param REQUEST_URI $request_uri;
        fastcgi_param DOCUMENT_URI $document_uri;
        fastcgi_param DOCUMENT_ROOT $document_root;
        fastcgi_param SERVER_PROTOCOL $server_protocol;

        fastcgi_param GATEWAY_INTERFACE CGI/1.1;
        fastcgi_param SERVER_SOFTWARE nginx/$nginx_version;

        fastcgi_param REMOTE_ADDR $remote_addr;
        fastcgi_param REMOTE_PORT $remote_port;
        fastcgi_param SERVER_ADDR $server_addr;
        fastcgi_param SERVER_PORT $server_port;
        fastcgi_param SERVER_NAME $server_name;

        # required if PHP was built with --enable-force-cgi-redirect
        fastcgi_param REDIRECT_STATUS 200;
    }
}
