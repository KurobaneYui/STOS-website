import os
import json
import datetime

import flask
from flask import Flask, request, redirect, send_file, url_for, session, render_template
from flask_apscheduler import APScheduler
from werkzeug.middleware.proxy_fix import ProxyFix

from flaskAjax.Users import Users

from flaskAjax.GroupManager import GroupManager
from flaskAjax.TeamManager import TeamManager
from flaskAjax.DataManager import DataManager
from flaskAjax.BaseComponents.Authorization import checkIfLogin
from flaskAjax.BaseComponents.DatabaseConnector import initialize_database

# set template_folder for 'render_template' function
app = Flask(__name__, template_folder="Frame/html5/", root_path="dist/")
scheduler = APScheduler()

# @app.route("/Frame/html5/<path:additionalURL>")
# def HTMLFrameRoutes(additionalURL):
#     if 'job' in session.keys():  # 以job判断一下session是否存有所需的信息
#         return render_template(
#             additionalURL,
#             department_id=session['department_id'],
#             department_name=session['department_name'],
#             job=session['job']
#         )
#     else:
#         return render_template(
#             additionalURL, department_id=0, department_name='预备队员', job=0
#         )

# @app.route("/css/<path:additionalURL>")
# def CSSRoutes(additionalURL):
#     return send_file("css/"+additionalURL)

# @app.route("/scss/<path:additionalURL>")
# def SCSSRoutes(additionalURL):
#     return send_file("scss/"+additionalURL)


@app.route("/favicon.ico")
def FaviconICORoutes():
    return send_file("favicon.ico")


@app.route("/imgs/<path:additionalURL>")
def ImgsRoutes(additionalURL):
    return send_file("imgs/" + additionalURL)


@app.route("/js/<path:additionalURL>")
def JSRoutes(additionalURL):
    return send_file("js/" + additionalURL)


@app.route("/assets/<path:additionalURL>")
def AssetsRoutes(additionalURL):
    return send_file("assets/" + additionalURL)


@app.route("/authentication/<path:additionalURL>", methods=["GET", "POST"])
def AuthenticationRoutes(additionalURL):
    if additionalURL == "login.html" and checkIfLogin():
        return redirect("/user_center/index.html")
    return send_file("authentication/" + additionalURL)


@app.route("/user_center/<path:additionalURL>", methods=["GET", "POST"])
def UsersRoutes(additionalURL):
    return send_file("user_center/" + additionalURL)


@app.route("/Users/TeacherCenter/<path:additionalURL>", methods=["GET", "POST"])
def TeachersRoutes(additionalURL):
    return send_file("Users/TeacherCenter/" + additionalURL)


@app.route("/tmpFiles/<path:additionalURL>", methods=["GET"])
def TmpFilesRoutes(additionalURL):
    return send_file("tmpFiles/" + additionalURL)


@app.route("/")
@app.route("/index.html")
def index():
    # return redirect("/authentication/login.html")
    return send_file("index.html")


# handle when '404 not found' error
@app.errorhandler(404)
def not_found_404(error):
    print("Try to access not exist URL: ", flask.request.url)
    return send_file("404.html"), 404


# handle when '405 not found' error
@app.errorhandler(405)
def not_found_405(error):
    print("Try to access URL with illegal method: ", flask.request.url)
    return send_file("405.html"), 405


# handle when '500 not found' error
@app.errorhandler(500)
def not_found_500(error):
    print("Try to access URL with illegal method: ", flask.request.url)
    return send_file("500.html"), 500


@scheduler.task("interval", id="delete_log", days=2)
def deleteLog(directory="./log", days=10):
    now = datetime.datetime.now()
    delta = datetime.timedelta(days=days)
    for file in os.listdir(directory):
        file_path = os.path.join(directory, file)
        if os.path.isfile(file_path):
            mtime = datetime.datetime.fromtimestamp(os.path.getmtime(file_path))
            if mtime < now - delta:
                os.remove(file_path)


@scheduler.task("interval", id="delete_tmpFiles", days=2)
def deleteTmpFiles(directory="./tmpFiles", days=10):
    now = datetime.datetime.now()
    delta = datetime.timedelta(days=days)
    for file in os.listdir(directory):
        file_path = os.path.join(directory, file)
        if os.path.isfile(file_path):
            mtime = datetime.datetime.fromtimestamp(os.path.getmtime(file_path))
            if mtime < now - delta:
                os.remove(file_path)


if __name__ == "__main__":
    with open("./config/STSA_APP.conf", "r") as f:
        config = json.load(f)
    app.config["PERMANENT_SESSION_LIFETIME"] = datetime.timedelta(
        **config["permanent_session_lifetime"]
    )
    app.config["SEND_FILE_MAX_AGE_DEFAULT"] = datetime.timedelta(
        **config["send_file_max_age_default"]
    )
    app.config["SCHEDULER_API_ENABLED"] = config["scheduler_api_enabled"]

    if config["proxy_enable"]:
        app.wsgi_app = ProxyFix(
            app.wsgi_app,
            x_for=config["x_for"],
            x_proto=config["x_proto"],
            x_host=config["x_host"],
            x_prefix=config["x_prefix"],
        )
    # set secret_key for starting the session
    app.secret_key = config["secret_key"].encode()
    # create necessary directories
    os.makedirs("./tmpFiles", exist_ok=True)
    os.makedirs("./log", exist_ok=True)
    # set scheduler
    scheduler.init_app(app)
    scheduler.start()
    # initialize database
    initialize_database()
    # Users package include ajax handler for user function
    Users(app)
    TeamManager(app)
    GroupManager(app)
    DataManager(app)

    @app.route("/shutdown", methods=["POST"])
    def shutdown():
        try:
            if request.get_json()["shutdown"] == config["shutdown"]:
                func = request.environ.get("werkzeug.server.shutdown")
                if func is None:
                    return "服务无法从此环境下关闭", 500
                func()  # 触发安全关闭
        except Exception:
            pass
        finally:
            return "服务关闭中…已接到关机指令。"

    # start a request
    if config["ssl_context"]:
        app.run(
            debug=config["debug"],
            threaded=config["threaded"],
            host=config["host"],
            port=config["port"],
            ssl_context=(config["cert"], config["key"]),
        )
    else:
        app.run(
            debug=config["debug"],
            threaded=config["threaded"],
            host=config["host"],
            port=config["port"],
        )
