#!/usr/bin/env python3

import sys
import json

f = open(sys.argv[1], "r")
argv = json.loads(f.read())
userAnswer = argv['0']
correctAnswer = argv['1']

print(userAnswer == correctAnswer,end='')