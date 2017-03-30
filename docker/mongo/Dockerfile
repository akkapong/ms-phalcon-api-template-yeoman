FROM mongo:3.0

COPY ["start_script.sh", "/"]
#COPY ["data_json/*", "/data/db_import/"]

RUN chmod +x /start_script.sh

CMD ["mongod"]
