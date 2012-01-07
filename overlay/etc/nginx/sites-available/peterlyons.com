server {
    listen 80 default;
    #This matches peterlyons.com and *.peterlyons.com
    server_name .peterlyons.com;
    #This is essential so we can use the same configuration in production and staging
    server_name_in_redirect off;
    access_log /var/log/nginx/peterlyons.com.access.log;
    error_log /var/log/nginx/peterlyons.com.error.log;
    error_page 404 /error404.html;
    error_page 502 /error502.html;

    location / {
        root /home/plyons/projects/peterlyons.com/public;
        index index.php index.html home.html;
        # this serves static files that exist without running other rewrite tests
        if (-f $request_filename) {
            expires 30d;
            break;
        }
        # this sends all non-existing file or directory requests to index.php
        #rewrite...last is safe to use here
        #http://wiki.nginx.org/IfIsEvil
        if (!-e $request_filename) {
            rewrite ^/(problog|persblog)(.+)$ /$1/index.php?q=$2 last;
        }
    }

    location /app/ {
        rewrite /app(.*) $1 break;
        proxy_pass http://localhost:9400;
    }


    # pass the PHP scripts to FastCGI server
    location ~ \.php$ {
        include /etc/nginx/fastcgi_params;
        fastcgi_pass 127.0.0.1:9200;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME /home/plyons/projects/peterlyons.com/public$fastcgi_script_name;  # same path as above
    }
}
