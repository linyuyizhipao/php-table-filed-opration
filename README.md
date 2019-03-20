# 208

## git 分支介绍
 * yun_1.0.1   208所最初的代码，企业都没有的那个版本
 
 * yun_1.1.0   具备企业，流量充值的版本
 
 * yun_1.1.0-release-corpus 术语库多稿件同时上传的版本，并且优化了部门
 
 
admin/controller/Admin.php   这是后台都继承然后在里面做了 所有请求都需要初始化对象的行为


admin/service   这里面包含各种对象封装

app/admin/controller/Install.php   这个是一个数据同步脚本，是为了解决版本迭代的时候应用平滑过渡所必须的数据维护


php项目服务端脚本介绍： 这个脚本中心意思就是  容器存在否 不存在直接镜像创建容器并启动
```php

#!/bin/sh
#set pe_app_server imageName
echo "****************************************************************"
echo "   beging  start up pebgmanager app...by rich 2018-08-25 18:00   "
echo "****************************************************************"
app_peserver="pebgmanager"
#red properties file contents
#begin create container
#create and start  pe service container
  #Determine whether this image exists.
  count=`docker ps -a |grep -w $app_peserver | wc -l`
  if [ $count -eq 0 ];  then #not exists container
    echo "detection "$app_peserver"  container not exists" 
  #begin create container
    echo "create  "$app_peserver" container ..."

  rm -rf /data/application/pebgmanager/208-php-background

    docker run -dit --name $app_peserver -p 8089:8089  $app_peserver:latest

        docker cp pebgmanager:/data/application/pebgmanager/208-php-background /data/application/transn/app/pebgmanager/

        docker rm -f pebgmanager

  #添加一个文件映射，如果工作代码为空，请自行再git拉代码
  docker run -dit -v /data/application/transn/app/pebgmanager/208-php-background:/data/application/pebgmanager/208-php-background --name ${app_peserver} -p 8089:8089 ${app_peserver}:latest

       #docker exec -dit $app_peserver  bash -c 'cd /data/application/pebgmanager/208-php-background && /usr/local/php/bin/php /tmp/composer.phar install'
  else  #exists 
    count=`docker ps |grep -w $app_peserver |wc -l`
    if [ $count -eq 0 ];  then    
       echo "start container  "$app_peserver" ..."
       docker start $app_peserver
    fi
   fi
        #copy config file to container
  echo "copy database file to container ..."
        docker cp $TRANSN_HOME/app/pebgmanager/config/database.php $app_peserver:/data/application/pebgmanager/208-php-background/app/
        #enter container and execute cmd
  echo "enter docker container app ..."
        docker exec -dit $app_peserver  bash -c 'cd /data/application/pebgmanager/ && sh start.sh'
        echo "startup app pebgmanager success ... "
```

### 下面这个脚本：  主要是想将服务器正在运行的容器动态打包成一个镜像，并把这个镜像生成一个tag包，
### 再把这个tag包上传的指定ip服务器地址的指定目录，并在该ip下的服务器将tag包load成镜像，运行成容器
### 再进入到容器中的指定目录，使用git拉去项目代码，初始化代码应用，启动容器里面的各项服务
```php

#将镜像tar包传到远程服务器指定文件夹目录
#遵从本地镜像tar抱在 /tmp/pebgmanager.tar    远程服务器在 /tmp/pebgmanager.tar
hostIp="10.5.110.226"
hostUser="root"
hostPasswd="iol8110254"
app_peserver="pebgmanager"

docker save ${app_peserver} > /tmp/pebgmanager.tar

scp /tmp/pebgmanager.tar ${hostUser}@${hostIp}:/tmp/${app_peserver}.tar

#ssh ${hostUser}@${hostIp} <<eeooff

#docker load < /tmp/pebgmanager.tar

#docker run -dit -v /data/application/transn/app/pebgmanager/208-php-background:/data/application/pebgmanager/208-php-background --name ${app_peserver} -p 8089:8089 ${app_peserver}:latest

#cd /data/application/transn/app/pebgmanager

#git clone -b yun_1.1.0 https://gitlab.iol8.com/IOL8-AI/yun-yike-qiye.git

#mv /data/application/transn/app/pebgmanager/yun-yike-qiye/* /data/application/transn/app/pebgmanager/208-php-background/

#rm -rf /data/application/transn/app/pebgmanager/yun-yike-qiye

#chmod -R 777 /data/application/transn/app/pebgmanager/208-php-background

#docker exec -it pebgmanager /bin/bash -c "cd /data/application/pebgmanager; ./start.sh;"

eeooff

echo "success"



scp /tmp/pebgmanager.tar root@10.5.110.223:/tmp/pebgmanager.tar



docker load < /data/pebgmanager.tar


docker run -dit -v /data/application/transn/app/pebgmanager/208-php-background:/data/application/pebgmanager/208-php-background --name pebgmanager -p 8089:8089 pebgmanager:latest


cd /data/application/transn/app/pebgmanager

git clone -b yun_1.1.0 https://gitlab.iol8.com/IOL8-AI/yun-yike-qiye.git
```


### php 服务器服务遵从规则

你会发现  /data/application/transn/app/pebgmanager  为php项目路径

             208-php-background  
             bin    //里面专放执行脚本的，这个服务脚本负责启动 pebgmanager 下的所有待启动的服务，并且它会被上一层总控制的脚本执行
             config  // 里面放的是项目源代码配置文件，其实这个是多语的  因为上面的容器启动中  文件路径我已经加了宿主映射
             dockerimage  //镜像的tar包
          

## 测试服务器 

* ssh root@10.5.110.226 -P 22         -p iol8110254
* 项目代码位置：/data/application/transn/app/pebgmanager/208-php-background
* nginx配置文件位置: /etc/nginx/vhost/pebgmanager.conf

ps -ef | grep nginx  找到nginx的pid
kill nginx的pid
whereis nginx  后发现  /usr/sbin/nginx 路径
/usr/sbin/nginx start 启动
以上万能服务控制方法

* 最后  whewreis nginx   可以帮助或许一切服务信息