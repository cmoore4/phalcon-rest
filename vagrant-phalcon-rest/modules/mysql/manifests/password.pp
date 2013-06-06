#
# Class: mysql::password
#
# Set mysql password
#
class mysql::password {

  # Load the variables used in this module. Check the params.pp file 
  require mysql
  require mysql::params

  if (!defined(File['/home/vagrant/root-mysql'])) {
    file { '/home/vagrant/root-mysql':
      path   => '/home/vagrant/root-mysql',
      ensure => directory
    }
  }

  file { '/home/vagrant/root-mysql/.my.cnf':
    ensure  => 'present',
    path    => '/home/vagrant/root-mysql/.my.cnf',
    mode    => '0644',
    owner   => 'vagrant',
    group   => 'root',
    content => template('mysql/root.my.cnf.erb'),
    require => File['/home/vagrant/root-mysql']
  }

  file { '/home/vagrant/root-mysql/.my.cnf.backup':
    ensure  => 'present',
    path    => '/home/vagrant/root-mysql/.my.cnf.backup',
    mode    => '0644',
    owner   => 'vagrant',
    group   => 'root',
    content => template('mysql/root.my.cnf.backup.erb'),
    replace => 'false',
    before  => [ Exec['mysql_root_password'] , Exec['mysql_backup_root_my_cnf'] ],
  }

  exec { 'mysql_backup_root_my_cnf':
    require     => Service['mysql'],
    path        => "/bin:/sbin:/usr/bin:/usr/sbin",
    unless      => 'diff /home/vagrant/root-mysql/.my.cnf /home/vagrant/root-mysql/.my.cnf.backup',
    command     => 'cp /home/vagrant/root-mysql/.my.cnf /home/vagrant/root-mysql/.my.cnf.backup ; true',
    before      => File['/home/vagrant/root-mysql/.my.cnf'],
  }


  exec { 'mysql_root_password':
    subscribe   => File['/home/vagrant/root-mysql/.my.cnf'],
    require     => Service['mysql'],
    path        => "/bin:/sbin:/usr/bin:/usr/sbin",
    refreshonly => true,
    command     => "mysqladmin --defaults-file=/home/vagrant/root-mysql/.my.cnf.backup -uroot password '${mysql::real_root_password}'",
  }

}
