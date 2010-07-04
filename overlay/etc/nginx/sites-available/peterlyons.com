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
    
    error_page 404 /error404.html;

    error_page 502 /error502.html;
    # redirect server error pages to the static page /50x.html
    #
    #error_page 500 502 503 504 /50x.html;
    #location = /50x.html {
    #    root /var/www/nginx-default;
    #}

    # pass the PHP scripts to FastCGI server
    location ~ \.php$ {
        include /etc/nginx/fastcgi_params;
        fastcgi_pass 127.0.0.1:9200;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME /var/www/peterlyons.com$fastcgi_script_name;  # same path as above

        # required if PHP was built with --enable-force-cgi-redirect
        fastcgi_param REDIRECT_STATUS 200;
    }
}

server {
    listen 443;
    access_log /var/log/nginx/ssl.peterlyons.com.access.log;
    error_log /var/log/nginx/ssl.peterlyons.com.error.log;
    ssl on;
    ssl_certificate /etc/nginx/sites-available/peterlyons.com.crt;
    ssl_certificate_key /etc/nginx/sites-available/peterlyons.com.key;
    keepalive_timeout 70;

    location / {
        auth_basic "Restricted";
        auth_basic_user_file htpasswd;
        proxy_pass http://127.0.0.1:9300;
    }
}
