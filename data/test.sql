//这是用来测试PDO事务处理实用的
create table if not exists userAccount(
	id tinyint unsigned auto_increment key,
	username varchar(20) not null unique,
	salary decimal(10, 2)
)engine=InnoDB;

insert into userAccount(username,money) values('imooc', 10000), ('king', 5000);

CREATE TABLE if not exists test2(
	id tinyint 
);