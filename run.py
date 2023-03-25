import json
from datetime import timedelta

import flask
from flask import Flask, request, redirect, send_file, url_for, abort, session, render_template
from werkzeug.middleware.proxy_fix import ProxyFix

from Frame.python3.Ajax.Users import Users
from Frame.python3.Ajax.GroupManager import GroupManager
from Frame.python3.Ajax.TeamManager import TeamManager
from Frame.python3.Ajax.DataManager import DataManager
from Frame.python3.BaseComponents.Authorization import checkIfLogin


app = Flask(__name__, template_folder="Frame/html5/") # set template_folder for 'render_template' function


@app.route("/Frame/html5/<path:additionalURL>")
def HTMLFrameRoutes(additionalURL):
    if 'job' in session.keys(): # 以job判断一下session是否存有所需的信息
        return render_template(additionalURL, department_id=session['department_id'], department_name=session['department_name'], job=session['job'])
    else:
        return render_template(additionalURL, department_id=0, department_name='预备队员', job=0)


@app.route("/css/<path:additionalURL>")
def CSSRoutes(additionalURL):
    return send_file("css/"+additionalURL)


@app.route("/scss/<path:additionalURL>")
def SCSSRoutes(additionalURL):
    return send_file("scss/"+additionalURL)


@app.route("/js/<path:additionalURL>")
def JSRoutes(additionalURL):
    return send_file("js/"+additionalURL)


@app.route("/assets/<path:additionalURL>")
def AssetsRoutes(additionalURL):
    return send_file("assets/"+additionalURL)


@app.route("/Users/Authentication/<path:additionalURL>", methods=['GET', 'POST'])
def LoginRoutes(additionalURL):
    if additionalURL == "login.html" and checkIfLogin():
        return redirect("/Users/UserCenter/index.html")
    return send_file("Users/Authentication/"+additionalURL)


@app.route("/Users/UserCenter/<path:additionalURL>", methods=['GET', 'POST'])
def UsersRoutes(additionalURL):
    return send_file("Users/UserCenter/"+additionalURL)


@app.route("/Users/TeacherCenter/<path:additionalURL>", methods=['GET', 'POST'])
def TeachersRoutes(additionalURL):
    return send_file("Users/TeacherCenter/"+additionalURL)


@app.route("/tmpFiles/<path:additionalURL>", methods=['GET'])
def TmpFilesRoutes(additionalURL):
    return send_file("tmpFiles/"+additionalURL)


@app.route("/")
@app.route("/index.html")
def index():
    return send_file("index.html")


# handle when '404 not found' error
@app.errorhandler(404)
def not_found_404(error):
    print("Try to access not exist URL: ", flask.request.url)
    return send_file("Frame/html5/404.html"), 404

# handle when '405 not found' error


@app.errorhandler(405)
def not_found_405(error):
    print("Try to access URL with illegal method: ", flask.request.url)
    return send_file("Frame/html5/405.html"), 405

# handle when '500 not found' error


@app.errorhandler(500)
def not_found_500(error):
    print("Try to access URL with illegal method: ", flask.request.url)
    return send_file("Frame/html5/500.html"), 500


if __name__ == "__main__":
    with open("./config/Flask.conf", 'r') as f:
        config = json.load(f)
    app.config["SEND_FILE_MAX_AGE_DEFAULT"] = timedelta(
        **config["send_file_max_age_default"])
    if config["proxy_enable"]:
        app.wsgi_app = ProxyFix(
            app.wsgi_app, x_for=config["x_for"], x_proto=config["x_proto"], x_host=config["x_host"], x_prefix=config["x_prefix"])
    # set secret_key for starting the session
    app.secret_key = config["secret_key"].encode()
    # Users package include ajax handler for user function
    Users(app)
    TeamManager(app)
    GroupManager(app)
    DataManager(app)
    # start a request
    if config["ssl_context"]:
        app.run(debug=config["debug"], threaded=config["threaded"],
                host=config["host"], port=config["port"],
                ssl_context=(config["cert"], config["key"]))
    else:
        app.run(debug=config["debug"], threaded=config["threaded"],
                host=config["host"], port=config["port"])
