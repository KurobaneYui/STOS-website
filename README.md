- [STSA website](#stsa-website)
  - [使用手册](#使用手册)
    - [环境搭建](#环境搭建)
    - [自定义配置（务必阅读，确保密码等信息的修改）](#自定义配置务必阅读确保密码等信息的修改)
      - [docker compose file](#docker-compose-file)
      - [网站配置文件](#网站配置文件)
    - [启动容器](#启动容器)
  - [开发手册](#开发手册)
    - [Docker镜像](#docker镜像)
    - [Python库依赖](#python库依赖)

# STSA website

本仓库用于督导队线上数据系统。

## 使用手册

### 环境搭建

> 推荐在Linux系统中使用。代码**不保证**没有系统耦合API，建议在新环境下充分测试

首先在系统中安装`docker`和`docker-compose`两个组件，具体安装方式请参考[docker官网](https://www.docker.com/get-started/)。

获取网站所需的两个镜像：`mysql`和`stsa_flask_server`。其中`stsa_flask_server`镜像的获取方式可以从下方二选一：

* 使用`docker build -f STSA_dockerfile.txt -t stsa_flask_server:latest <本项目根目录>`构建docker镜像，生成`stsa_flask_server:latest`镜像用于后续部署
* 使用已经生成的docker镜像则用命令`docker pull kurobaneyui/stsa_flask_server:latest`

> **不保证**个人仓库镜像的后续维护，在熟悉docker工具后请自行构建镜像。

### 自定义配置（务必阅读，确保密码等信息的修改）

#### docker compose file

在完成镜像的构建或下载后，使用根目录下的`docker-compose.yml`文件利用compose组件构建容器。

在启动容器前，请先打开文件自定义部分参数，下文中`xxx.yyy = ddd`指

```yml
services:
    xxx:
        yyy: ddd
```

或

```yml
services:
    xxx:
        yyy:
        - ddd
```

请根据docker compose file的语法自行确定。

请至少确保修改：

* `STSA-Database.environment.MYSQL_ROOT_PASSWORD`为新的密码

可选的修改：

* `STSA-server.ports = 外部端口:内部端口`：网站容器外部端口号和内部端口号。第一项外部端口号为访问网站所需的端口，如配合`nginx`使用，请确保有相应的修改；第二项内部端口是容器内flask提供服务的端口，请确保和下文中flask配置中的端口保持一致。
* `STSA-Database.ports = 外部端口:3306`：数据库容器的内外端口号。如果需要外部直接访问数据库做维护，自行确定外部端口；如果无需外部维护，可删除此项。
* `STSA-server.volumes`和`STSA-Database.volumes`：可以配置容器文件是否对直接与主机共享或直接使用已有文件（请阅读与docker volumes配置相关的手册以确保你知道在做什么）。对于`STSA-server`而言，内部目录位于容器`/home/STSA`，对于`STSA-Database`而言，数据库目录位于`/var/lib/mysql`

其余配置请在确保知道自己了解的情况下修改

#### 网站配置文件

本网站基于`python3`，网站配置文件主要针对使用到的`flask`框架、`pymysql`库，和`flask_apscheduler`库。配置文件位于`/config/STSA_APP.conf`，使用json语法。

配置文件各条含义、配置与默认值如下表

|            名称            |  类型  |                  含义                   |                      默认配置                       |                                                                                备注                                                                                |
| :------------------------: | :----: | :-------------------------------------: | :-------------------------------------------------: | :----------------------------------------------------------------------------------------------------------------------------------------------------------------: |
|           debug            |  bool  |        是否启用flask的debug模式         |                        false                        |                 通常任何运行时错误（文件不存在、python运行时错误等）都会返回相应的错误代码（404、500等）。<br/>debug模式下会返回python的异常追踪栈                 |
|          threaded          |  bool  |          是否启用flask的多线程          |                        true                         |                                                                              建议开启                                                                              |
|            host            | string |          需要flask监听的IP列表          |                      `0.0.0.0`                      |                                                                       `0.0.0.0`表示监听所有                                                                        |
|            port            |  int   |        由flask对外提供服务的端口        |                        5000                         |                                                       请确保和上文docker compose file配置的内部端口保持一致                                                        |
|        proxy_enable        |  bool  |            是否处于反向代理             |                        true                         |                                                             如果由nginx等服务反向代理，请配置为`true`                                                              |
| permanent_session_lifetime |  dict  |        session在无操作后多久失效        |  {"seconds": 0, "hours": 1, "days": 0, "weeks": 0}  | 通常session在浏览器关闭后自动失效但打开时长期有效。通过配置时间间隔，可以让session在指定时间后失效而不受浏览器状态的影响，失效时间从每一次和服务器交互开始归零重计 |
| send_file_max_age_default  |  dict  |         发送的文件的缓存有效期          | {"seconds": 0.5, "hours": 0, "days": 0, "weeks": 0} |                            发送的文件最大缓存时间是利用了和浏览器沟通的缓存机制，指定时间间隔后如果在时间范围内则使用缓存，否则重新获取                            |
|           x_for            |  int   |                                         |                          1                          |                                                                         根据flask手册配置                                                                          |
|          x_proto           |  int   |                                         |                          1                          |                                                                         根据flask手册配置                                                                          |
|           x_host           |  int   |                                         |                          1                          |                                                                         根据flask手册配置                                                                          |
|          x_prefix          |  int   |                                         |                          1                          |                                                                         根据flask手册配置                                                                          |
|         secret_key         | string | 使用session和cookie功能所需的自定义密钥 |         _5#asdf2IJOknieri889345L'F4Q8zec]/          |                                                                                                                                                                    |
|        ssl_context         |  bool  |          是否启用SSL加密上下文          |                        false                        |                                                                                                                                                                    |
|            cert            | string |              证书文件路径               |                         ""                          |                                                                                                                                                                    |
|            key             | string |            证书密钥文件路径             |                         ""                          |                                                                                                                                                                    |
|           DBhost           | string |         数据库服务IP或本地域名          |                                                     |                                                                                                                                                                    |
|           DBport           |  int   |            数据库服务端口号             |                                                     |                                                                                                                                                                    |
|           DBuser           | string |            数据库服务用户名             |                                                     |                                                                                                                                                                    |
|         DBpassword         | string |             数据库服务密码              |                                                     |                                                                                                                                                                    |
|         DBdatabase         | string |            使用的数据库名称             |                                                     |                                                                                                                                                                    |
|           logDir           | string |              日志文件目录               |                        ./log                        |                                                          所有日志文件均位于此文件夹下并按分钟分为不同文件                                                          |
|          logMode           | string |                日志模式                 |                       WARNING                       |                                        使用python的logging库，可可选为`NOTSET`,`DEBUG`,`INFO`,`WARNING`,`ERROR`,`CRITICAL`                                         |

### 启动容器

请在项目根目录下运行`docker compose up -d`启动所有所需组件。docker会自动创建所需内网交换机并根据配置运行相应容器。可以通过`docker ps -a`查看运行的容器，对于状态不是`Up`的容器可以使用`docker log inspect <容器ID或名称>`查看容器日志以定位错误。

## 开发手册

### Docker镜像

根目录下docker-compose.yml文件为docker compose配置文件，使用前请配置：

* 卷宗目录
* 暴露端口号
* MySQL密码

### Python库依赖

* openpyxl: 用于处理Excel文件数据
  * lxml：用于加速大文件处理的依赖
  * pillow：用于提供图片支持的依赖
* pymysql: 用于和数据库通信
* pandas: 用于处理Excel导入的数据等
* numpy: 用于查早查课任务安排
* scipy：用于查早查课任务安排
* flask: 网页服务框架
  * flask_apscheduler：在flask服务中提供定时任务的工具