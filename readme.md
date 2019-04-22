## Intro

A restful php framework powered by CodeIgniter v3.1.

## Usage

```
# require php7.0+
composer install
```

#### configure .env

```
# mv .env.example .env
# TOKEN过期时间（天）
TOKEN_EXPIRE_DAY=7
# TOKEN加密key
TOKEN_KEY=ipets

# SNOW_FLAKE算法
SNOW_FLAKE_MACHINE_ID=1

# 七牛相关配置
QINIU_ACCESS_KEY=
QINIU_SECRET_KEY=
QINIU_BUCKET=
QINIU_CDN_URL=https://cdn.xxx.com/

# 阿里大禹（短信）配置
ALISMS_ACCESS_KEY=
ALISMS_ACCESS_SECRET=
ALISMS_SIGNNAME=
ALISMS_TEMPLATE_CODE=

# 加密-解密需要（如前端用公钥加密密码进行传输）
RSA_PRIVATE_KEY_PATH=../openssl-rsa-store/private_key.pem
RSA_PUBLIC_KEY_PATH=../openssl-rsa-store/rsa_public_key.pem

# 小程序相关配置
WX_APPID=
WX_APPSECRET=
```

## Nginx conf

```
server {
  listen 80;
  root /var/www/circle-api/public;
  index index.php index.html;
  server_name _;

  location / {
    try_files $uri $uri/ /index.php?$query_string;
  }

  location ~ \.php$ {
    include snippets/fastcgi-php.conf;
    fastcgi_pass unix:/run/php/php7.0-fpm.sock;
  }
}
```

## Thanks to

[codeigniter-restserver](https://github.com/chriskacerguis/codeigniter-restserver)

## License

The MIT License (MIT)
