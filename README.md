# ClubVote
社团投票系统
不含config文件夹下文件

待完善：
对model的理解有误 model文件夹里面都是实现的逻辑 应该叫service才对

而class文件夹下都是一些静态类用于填充数据的 应该有更好的方法去解决这个问题

index.php 文件应该改名为 routes.php 用于控制路由

里面的一堆require要去掉 通过命名空间+autoload机制解决
