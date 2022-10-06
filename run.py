import flask
from flask import Flask, request, redirect, send_file, url_for, abort, session, render_template
from Ajax.Users import Users
from Frame.python3.Authorization import checkIfLogin
from datetime import timedelta


# set template_folder for 'render_template' function
app = Flask(__name__, template_folder="Frame/html5/")
# set secret_key for starting the session
app.config["SEND_FILE_MAX_AGE_DEFAULT"] = timedelta(seconds=0.5)


@app.route("/Frame/html5/<path:additionalURL>")
def HTMLFrameRoutes(additionalURL):
    return render_template(additionalURL)


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


@app.route("/Users/<path:additionalURL>", methods=['GET','POST'])
def UsersRoutes(additionalURL):
    if additionalURL == "Authentication/login.html" and checkIfLogin()["data"]:
        return redirect("/Users/UserCenter/index.html")
    return send_file("Users/"+additionalURL)


@app.route("/")
@app.route("/index.html")
def index():
    return send_file("index.html")


# handle when '404 not found' error
@app.errorhandler(404)
def not_found(error):
    print("Try to access not exist URL: ", flask.request.url)
    return render_template("404.html"), 404

# handle when '405 not found' error
@app.errorhandler(405)
def not_found(error):
    print("Try to access URL with illegal method: ", flask.request.url)
    return render_template("405.html"), 405


if __name__ == "__main__":
    # Users package include ajax handler for user function
    Users(app)
    # start a request
    app.run(debug=True, threaded=True, host="127.0.0.1", port=5000)