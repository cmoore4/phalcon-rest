define mysql::query (
  $mysql_query,
  $mysql_db             = undef,
  $mysql_user           = 'root',
  $mysql_password       = '',
  $mysql_host           = 'localhost',
  $mysql_query_filepath = '/root/puppet-mysql'
  ) {

  file { "mysqlquery-${name}.sql":
    ensure  => present,
    mode    => 0600,
    owner   => root,
    group   => root,
    path    => "${mysql_query_filepath}/mysqlquery-${name}.sql",
    content => template('mysql/query.erb'),
    notify  => Exec["mysqlquery-${name}"],
    require => Service['mysql'],
  }


  $arg_mysql_user = $mysql_user ? {
    ''      => '',
    default => "-u ${mysql_user}",
  }

  $arg_mysql_host = $mysql_host ? {
  ''      => '',
  default => "-h ${mysql_host}",
  }

  $arg_mysql_password = $mysql_password ? {
    ''      => '',
    default => "--password=\"${mysql_password}\"",
  }

  exec { "mysqlquery-${name}":
    command     => "mysql --defaults-file=/root/.my.cnf \
                    ${arg_mysql_user} ${arg_mysql_password} ${arg_mysql_host} \
                    < ${mysql_query_filepath}/mysqlquery-${name}.sql",
    require     => File["mysqlquery-${name}.sql"],
    refreshonly => true,
    subscribe   => File["mysqlquery-${name}.sql"],
    path        => [ '/usr/bin' , '/usr/sbin' ],
  }

}
