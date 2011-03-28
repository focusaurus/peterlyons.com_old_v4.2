#!/usr/bin/python
import json
import os

packagePath = os.path.join(os.path.dirname(__file__), "../package.json")
package = json.load(open(packagePath))
for key in package["depedencies"].keys():
    print key
