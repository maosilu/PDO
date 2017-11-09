创建的存储过程：
delimiter //
create procedure test1()
	Begin
	select * from pdo_user;
	select * from users;
END
//
delimiter ;