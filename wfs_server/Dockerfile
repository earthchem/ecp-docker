FROM tomcat:9.0.104-jre17-temurin-noble

RUN apt-get update && apt-get install -y unzip && rm -rf /var/lib/apt/lists/*

COPY ./wfs_server/geoserver-2.27.0.war /usr/local/tomcat/webapps/wfs.war

RUN mkdir -p /usr/local/tomcat/webapps/wfs && \
    unzip -q /usr/local/tomcat/webapps/wfs.war -d /usr/local/tomcat/webapps/wfs && \
    rm /usr/local/tomcat/webapps/wfs.war

# Copy geoserver configuration
COPY ./wfs_server/geoserver-users.xml /usr/local/tomcat/webapps/wfs/data/security/usergroup/default/users.xml

# Copy the ECP directory to the corresponding workspaces location
COPY ./wfs_server/ECP /usr/local/tomcat/webapps/wfs/data/workspaces/ECP

# Copy tomcat configuration
COPY ./wfs_server/server.xml /usr/local/tomcat/conf/server.xml
COPY ./wfs_server/tomcat-users.xml /usr/local/tomcat/conf/tomcat-users.xml

EXPOSE 8080

CMD ["catalina.sh", "run"]
