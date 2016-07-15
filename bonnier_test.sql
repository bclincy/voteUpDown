create database bonnier;
use bonnier;
create table vote (article_id int, score int);
ALTER TABLE `vote` ADD PRIMARY KEY( `article_id`);
