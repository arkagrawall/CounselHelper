import mysql.connector
from mysql.connector import Error

def create_connection(host_name, user_name, user_password, db_name):
    connection = None
    try:
        connection = mysql.connector.connect(
            host=host_name,
            user=user_name,
            passwd=user_password,
            database=db_name
        )
        print("Connection to MySQL DB successful")
    except Error as e:
        print(f"The error '{e}' occurred")

    return connection

def execute_query(connection, query):
    cursor = connection.cursor()
    try:
        cursor.execute(query)
        connection.commit()
        print("Query executed successfully")
    except Error as e:
        print(f"The error '{e}' occurred")

def fetch_distinct_colleges_branches(connection):
    cursor = connection.cursor()
    query = "SELECT DISTINCT Institute, Branch FROM csab"
    try:
        cursor.execute(query)
        result = cursor.fetchall()
        return result
    except Error as e:
        print(f"The error '{e}' occurred")
        return None

def insert_into_csabdata(connection, data):
    cursor = connection.cursor()
    insert_query = "INSERT INTO csabdata (collegename, branch) VALUES (%s, %s)"
    try:
        cursor.executemany(insert_query, data)
        connection.commit()
        print("Data inserted successfully into csabdata")
    except Error as e:
        print(f"The error '{e}' occurred")

# Database credentials
host_name = "localhost"
user_name = "root"
user_password = ""
db_name = "aktu"

# Connect to the database
connection = create_connection(host_name, user_name, user_password, db_name)

# Create the csabdata table
create_table_query = """
CREATE TABLE IF NOT EXISTS `csabdata` (
  `collegename` varchar(114) NOT NULL,
  `branch` varchar(163) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
"""
execute_query(connection, create_table_query)

# Fetch distinct college and branch combinations
distinct_colleges_branches = fetch_distinct_colleges_branches(connection)

# Insert the results into csabdata table
if distinct_colleges_branches:
    insert_into_csabdata(connection, distinct_colleges_branches)
else:
    print("No distinct college and branch combinations found.")
