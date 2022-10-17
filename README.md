# STSA website

## 运行环境

### 第一步：主机环境安装

1. 安装docker和docker-compose两个组件，具体安装方式请参考docker官网文档
2. 安装git以同步网站代码
3. 安装vim以便捷的进行必要的修改

### 第二步：Docker

根目录下docker-compose.yml文件为docker compose配置文件，使用前请配置：

* 卷宗目录
* 暴露端口号
* MySQL密码

配置完成后，在docker-compose.yml同级目录下运行 `docker compose up -d`

启动容器组后请使用 `docker network ls` 查看新建的网桥，并利用 `docker network inspect <bridge id>` 命令查看MySQL服务器的IP地址

### 第三步：启动flask

启动本项目前，请确保一下配置正确：

* 前往 `./config/DataBase_STSA.conf` 文件修改数据库IP和密码等信息
* 前往 `./config/Flask.conf` 文件修改flask运行时配置
* 前往 `./config/Log.conf` 文件修改本项目的log模式和目录

在docker的服务器容器内，进入网站根目录（包含 `run.py` 和 `README.md` 等文件的目录），运行 `python3 ./run.py`

确认数据均无问题，可以使用 `nohup python3 ./run.py &`命令重新启动项目，并正常退出即可

## 开发环境

### Python部分

* xlrd: openpyxl配套库
* xlwd: openpyxl配套库
* openpyxl: 用于处理Excel文件数据
* mysql-connector-python: 用于和数据库通信
* pandas: 用于处理Excel导入的数据等
* numpy: pandas配套库
* flask: 网页服务框架

### nginx部分
