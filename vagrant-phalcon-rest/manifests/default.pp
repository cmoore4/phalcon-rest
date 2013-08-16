/**
 * LAMP Stack Puppet Config - PO System
 * Creator: Sean Moore
 * Date: 05-29-2013
 */

/*  Tun apt-get update before anything, set some timeouts */
exec { 'apt-get update':
  command => 'apt-get update',
  path    => '/usr/bin/',
  timeout => 60,
  tries   => 3,
}

/*  Allows you to add ppa's via puppet.  After added, updates them. */
class { 'apt':
  always_apt_update => true,
}

/*  Installs package from the package manager. Needs apt-get update before running. */
package { ['python-software-properties']:
  ensure  => 'installed',
  require => Exec['apt-get update'],
}

/*  Creates the file from shared source, then ensures it exists. */
file { '/home/vagrant/.bash_aliases':
  source => 'puppet:///modules/puphpet/dot/.bash_aliases',
  ensure => 'present',
}

/* Install more pakages via package manager. */
package { ['build-essential', 'vim', 'curl', 'git', 'gcc', 'autoconf']:
  ensure  => 'installed',
  require => Exec['apt-get update'],
}

host { "api.example.local":
  ip => "127.0.0.1",
}

host { "example.local":
  ip => "127.0.0.1",
}

/* Declare the apache class. */
class { 'apache': }


/* This, I don't know. */
apache::dotconf { 'custom':
  content => 'EnableSendfile Off',
}

/* Enable these specific non-default modules */
apache::module { 'rewrite': }
apache::module { 'headers': }

/* Set up our vhosts */
apache::vhost { 'api.example.local':
  server_name   => 'api.example.local',
  docroot       => '/var/www/project',
  port          => '80',
  env_variables => ['APP_ENV dev'],
  directory_allow_override => 'All'
}


/*  Add a ppa for PHP 5.4 */
apt::ppa { 'ppa:ondrej/php5-oldstable':
  before  => Class['php'],
}

/*  Set up php with Apache.  Requires apache puppet package first.*/
class { 'php':
  service => 'apache',
  require => Package['apache'],
}

/*  Download and install these php modules from apt-get */
php::module { 'php5-cli': }
php::module { 'php5-curl': }
php::module { 'php5-intl': }
php::module { 'php5-mcrypt': }
php::module { 'php5-mysql': }
php::module { 'php5-ldap': }

class { 'php::devel':
  require => Class['php'],
}

class { 'php::pear':
  require => Class['php'],
}


/* This should install XDebug, but was throwing errors. */

/*class { 'xdebug':
  service => 'apache',
}

xdebug::config { 'cgi': }
xdebug::config { 'cli': }*/

/*  Get composer */
class { 'php::composer': }

/*  Set some php ini values. */
php::ini { 'php':
  value  => ['date.timezone = "America/Chicago"'],
  target => 'php.ini',
  service => 'apache',
}
php::ini { 'custom':
  value  => ['display_errors = On', 'error_reporting = -1'],
  target => 'custom.ini',
  service => 'apache',
}
