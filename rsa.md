```
# test on ubuntu 14.04
apt-get install openssl
apt-get install php7.2-openssl
openssl genrsa -out rsa_private_key.pem 1024
openssl pkcs8 -topk8 -inform PEM -in rsa_private_key.pem -outform PEM -nocrypt -out private_key.pem
openssl rsa -in rsa_private_key.pem -pubout -out rsa_public_key.pem
```
