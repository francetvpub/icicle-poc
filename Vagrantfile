# -*- mode: ruby -*-
# vi: set ft=ruby :

VAGRANTFILE_API_VERSION = '2'

@script = <<SCRIPT
set -ex

ZEND_ADMIN_PASSWORD="password"
ZEND_BOOTSTRAP_PRODUCTION=false
ZEND_DEBUG=true

echo "{\\
    \\"ZEND_DEBUG\\":$ZEND_DEBUG,\\
    \"ZEND_BOOTSTRAP_PRODUCTION\":$ZEND_BOOTSTRAP_PRODUCTION,\
    \"ZEND_ADMIN_PASSWORD\":\"$ZEND_ADMIN_PASSWORD\"\
    }" > /tmp/state

if [ ! -f /etc/apt/sources.list.d/zend.list ] ;
then
  echo "Installing Zend Server"
  wget http://repos.zend.com/zend.key -O- | apt-key add -
  echo "deb http://repos.zend.com/zend-server/9.0.0/deb_apache2.4 server non-free" > /etc/apt/sources.list.d/zend.list
  
  apt-get update
  apt-get upgrade -y
  apt-get install -y zend-server-php-7.0 curl git-core
  
  echo "" > /etc/profile.d/zend.sh
  echo "PATH=\\$PATH:/usr/local/zend/bin" >> /etc/profile.d/zend.sh
  echo "LD_LIBRARY_PATH=\\$LD_LIBRARY_PATH:/usr/local/zend/lib" >> /etc/profile.d/zend.sh
  source /etc/profile
  
  ZS_INIT_VERSION="0.2"
  ZS_INIT_SHA256="1c5cf557daf48cf018dba1cf46208f215d3b5fab47c73ff2d39988581ebd6932"
  curl -fSL -o zs-init.tar.gz "http://repos.zend.com/zs-init/zs-init-docker-${ZS_INIT_VERSION}.tar.gz" \
      && echo "${ZS_INIT_SHA256} *zs-init.tar.gz" | sha256sum -c - \
      && mkdir /usr/local/zs-init \
      && tar xzf zs-init.tar.gz --strip-components=1 -C /usr/local/zs-init \
      && rm zs-init.tar.gz
fi

echo "Installing composer and running composer install"
if [ -f /usr/local/bin/composer ] ;
then
  composer self-update
else
  curl -Ss https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
  cd /usr/local/zs-init
  composer install --no-interaction
  
  echo "Bootstrapping Zend Server"
  
  echo "{\\
    \\"ZEND_DEBUG\\":$ZEND_DEBUG,\\
    \\"ZEND_BOOTSTRAP_PRODUCTION\\":$ZEND_BOOTSTRAP_PRODUCTION,\\
    \\"ZEND_ADMIN_PASSWORD\\":\\"$ZEND_ADMIN_PASSWORD\\"\\
    }" > data/state
  cp /var/www/zf/zend.lic /etc/zend.lic
  ./init.php
fi

set +e
set -e

echo "** [POC] Visit http://192.168.40.10:10081 in your browser for Zend Server console **"
echo "** [POC] Zend Server admin password: $ZEND_ADMIN_PASSWORD"

SCRIPT

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = 'ubuntu/trusty64'
  config.vm.network "private_network", ip: "192.168.40.10"
  config.vm.hostname = "zf.local"
  config.vm.synced_folder '.', '/var/www/zf'
  config.vm.provision 'shell', inline: @script

  config.vm.provider "virtualbox" do |vb|
    vb.customize ["modifyvm", :id, "--memory", "2048"]
  end

end