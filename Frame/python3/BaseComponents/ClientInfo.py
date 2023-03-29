import json
import requests
from flask import request


class ClientInfo():
    @staticmethod
    def getInfo() -> dict:
        """Detect client infos about IP, address, client agent and accept language

        This function use flask.request to get IP and info of client agent.
        Function use online API to check address of IP.
        Normally use https://whois.pconline.com.cn to detect address.
        Use http://ip-api.com for detection when catch a timeout error while using URL above.

        Returns:
            dict: Return value is a dict and includes keys below:
                    "IP", "address", "language" and "agent".
                Value of "address" key is also a dict. It has a "url" key to indication the online API.
        """

        # get info from flask.request
        ip = request.remote_addr
        language = request.accept_languages
        agent = request.user_agent
        # detect address of IP
        try:
            address = json.loads(requests.get(
                f"https://whois.pconline.com.cn/ipJson.jsp?json=true&ip={ip}", timeout=1).text)
            address["url"] = "https://whois.pconline.com.cn"
        except (requests.exceptions.ReadTimeout, requests.exceptions.ProxyError):
            try:
                address = json.loads(requests.get(
                    f"http://ip-api.com/json/{ip}?lang=zh-CN", timeout=1).text)
                address["url"] = "http://ip-api.com"
            except (requests.exceptions.ReadTimeout, requests.exceptions.ProxyError):
                print(
                    "Cannot connect to https://whois.pconline.com.cn nor http://ip-api.com.")
                address = {"url": ""}
        return {
            "IP": ip,
            "address": address,
            "language": language,
            "agent": str(agent)
        }
