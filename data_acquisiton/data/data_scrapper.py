import json
import csv

if __name__ == "__main__":
    with open("trump_data.csv", "w") as csv_file: 
        writer = csv.writer(csv_file)
        writer.writerow(["ID", "Text", "User ID", "User Name", "User Screen Name", "User Location", "Source"])

    with open("data/twitter-sample-1.txt", mode="r") as fp:
        csv_store = [None] * 10

        for row in fp:
            data = json.loads(row)
            csv_store[0] = data["id"]
            csv_store[1] = data["text"]
            csv_store[2] = data["user"]["id"]
            csv_store[3] = data["user"]["name"]
            csv_store[4] = data["user"]["screen_name"]
            csv_store[5] = data["user"]["location"]
            csv_store[6] = data["source"] #you can see the type of device theyre using
          


    with open("data.csv", "a") as csv_file: 
        writer = csv.writer(csv_file)
        writer.writerow(csv_store)
      
    # print(csv_store)



    # with open("twitter-db.csv", "w+") as CSV_file:
    #     writer = csv.writer(CSV_file)
    #     csv.writerrows(csv_store)
 

 #hashtag as a fingerprint "filter it out "