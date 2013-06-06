define mysql::queryfile (
  $mysql_file,
  $mysql_db             = undef,
  $mysql_user           = 'root',
  $mysql_password       = '',
  $mysql_host           = 'localhost',
  $mysql_query_filepath = '/root/puppet-mysql'
  ) {

  if ! defined(File[$mysql_query_filepath]) {
    file { $mysql_query_filepath:
      ensure => directory,
    }
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

  exec { "mysqlqueryfile-${name}":
    command => "mysql --defaults-file=/root/.my.cnf \
                ${arg_mysql_user} ${arg_mysql_password} ${arg_mysql_host} \
                ${mysql_db} < ${mysql_file} && touch ${mysql_query_filepath}/mysqlqueryfile-${name}.run",
    path    => [ '/usr/bin' , '/usr/sbin' , '/bin' , '/sbin' ],
    creates => "${mysql_query_filepath}/mysqlqueryfile-${name}.run",
    unless  => "ls ${mysql_query_filepath}/mysqlqueryfile-${name}.run",
    require => File[$mysql_query_filepath],
  }

}
