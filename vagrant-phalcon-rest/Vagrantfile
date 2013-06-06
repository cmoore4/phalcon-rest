Vagrant.configure("2") do |config|
  config.vm.box = "precise64"
  config.vm.box_url = "http://files.vagrantup.com/precise64.box"

  config.vm.network :private_network, ip: "192.168.56.179"
  config.ssh.forward_agent = true

  config.vm.provider :virtualbox do |v|
    v.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
    v.customize ["modifyvm", :id, "--memory", 1024]
  end

  
  config.vm.synced_folder "../", "/var/www/project", id: "project-all"  
  config.vm.provision :puppet do |puppet|
    puppet.manifests_path = "manifests"
    puppet.module_path = "modules"
    puppet.options = ['--verbose']
  end

  config.vm.provision :shell, :path => "shell/phalcon.sh"
end
