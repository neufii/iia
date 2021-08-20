# -*- coding: utf-8 -*-

import numpy as np
import random
import json
import sys

f = open(sys.argv[1], "r")
argv = json.loads(f.read())
question1 = argv['0']
question2 = argv['1']

json1 = json.loads(question1)
json2 = json.loads(question2)

def processJSON(jsonData):
    arr = []
    for item in jsonData[1:]:
        if(item['type'] == 'number'):
            arr.append(item['content'])
        elif(item['type'] == 'other'):
            continue
        else:
            arr.append(item['type'])
    return arr;

def jaccardDistance(list1, list2):
    intersection = len(list(set(list1).intersection(list2)))
    union = (len(set(list1)) + len(set(list2))) - intersection
    return 1-(float(intersection) / union)

processed1 = processJSON(json1)
processed2 = processJSON(json2)


print(jaccardDistance(processed1,processed2))