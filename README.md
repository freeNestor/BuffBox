# BuffBox

This is a web application that has simple functions may help IT engineer.
I like coding,especially PHP.
So I use PHP language and created a project called BuffBox which include functions like Rundeck tools.
I'm not very familiar with coding and web project,so there is no api,just remove some weakness Rundeck has.

#Software Used
1. PHP 7
2. Lavarel Framework
3. MySQL 5.7.11
4. Nginx

#Installation
1. MySQL 5.7.11
   I used version 5.7.11 and compiled myself.First,you need to download MySQL source file like mysql-5.7.11.tar.gz.
Install cmake and download boost before compile MySQL,so my command of compiling MySQL like this:
   tar xvf mysql-5.7.11.tar.gz
   cd mysql-5.7.11
   cmake. -DDOWNLOAD_BOOST=1 -DWITH_BOOST=/tmp -DWITH_INNOBASE_STORAGE_ENGINE=1 -DDEFAULT_CHARSET=utf8 -DDEFAULT_COLLATION=utf8_general_ci
   make && make install
defualt installation path is /usr/local/mysql
