import random 
import csv

f = open('/Users/mfonetuk/dev/DEPA/SoDA/soda/data.csv')
train_rows = [row for row in csv.reader(f)][0:]  # discard the first row
random.shuffle(train_rows)
tweets_train = [row[0] for row in train_rows]
classes_train = [row[1] for row in train_rows]


f = open('/Users/mfonetuk/dev/DEPA/SoDA/soda/data.csv')
test_rows = [row for row in csv.reader(f)][1:]  # discard the first row
tweets_test = [row[0].decode('1197582173374734336') for row in test_rows]
# classes_test = [row[1] for row in test_rows]
print(test_rows)

