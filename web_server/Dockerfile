FROM debian:bookworm-slim

RUN apt-get update -y \
	&& apt-get install -y \
    apt-transport-https \
	lsb-release \
	ca-certificates \
	wget \
    cmake \
    libpng-dev \
	libjpeg-dev \
	libgeos-dev \
    libgd-dev \
    libgdal-dev \
    python3 \
    python3-psycopg2 \
    python3-matplotlib \
    && mkdir /srv/app
RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg \
    && sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list' \
    && apt-get update -y

RUN apt-get install -y \
    php5.6 \
    php5.6-dev \
    php5.6-bz2 \
    php5.6-bcmath \
    php5.6-pgsql \
    php5.6-gd \
    php5.6-mbstring \
    php5.6-zip

RUN echo 'memory_limit=4G' > /etc/php/5.6/apache2/conf.d/20-memory-limit.ini \
    && echo 'error_reporting = E_ALL & ~E_WARNING & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT' > /etc/php/5.6/apache2/conf.d/20-error_reporting.ini \
    && echo 'display_errors = On' > /etc/php/5.6/apache2/conf.d/20-display_errors.ini \
    && echo 'short_open_tag = On' > /etc/php/5.6/apache2/conf.d/20-short-tags.ini \
    && echo 'memory_limit=4G' > /etc/php/5.6/cli/conf.d/20-memory-limit.ini \
    && echo 'error_reporting = E_ALL & ~E_WARNING & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT' > /etc/php/5.6/cli/conf.d/20-error_reporting.ini \
    && echo 'display_errors = On' > /etc/php/5.6/cli/conf.d/20-display_errors.ini \
    && echo 'short_open_tag = On' > /etc/php/5.6/cli/conf.d/20-short-tags.ini \
    && echo 'include_path=".:/usr/share/php:/usr/share"' > /etc/php/5.6/apache2/conf.d/20-include_path.ini \
    && echo "ServerName earthchemportal" >> /etc/apache2/apache2.conf \
    && chown -R www-data:www-data /srv/app 

# COPY fonts/* /usr/share/fonts/truetype/
# COPY apache.conf /etc/apache2/sites-available/000-default.conf
# COPY mapserv/* /mapserv-work/
COPY ./web_server/fonts/* /usr/share/fonts/truetype/
COPY ./web_server/apache.conf /etc/apache2/sites-available/000-default.conf
COPY ./web_server/mapserv/* /mapserv-work/
COPY ./web_server/dbconfig/* /dbconfig/
COPY ./web_server/www/. /srv/app/www/

RUN cd /mapserv-work \
    && tar -xvzf proj-4.9.3.tar.gz \
    && cd proj-4.9.3/ \
    && ./configure --prefix=/usr/local/proj4 \
    && make \
    && make install

RUN a2enmod rewrite \
	&& a2enmod headers \
	&& a2enmod proxy \
	&& a2enmod proxy_http \
	&& cd /mapserv-work \
	&& tar xvf mapserver-7.2.0.tar.gz \
	&& cd mapserver-7.2.0/ \
	&& mkdir build \
	&& cd build \
	&& cmake -Wno-dev \
	    -DCMAKE_INSTALL_PREFIX=/opt \
        -DCMAKE_PREFIX_PATH=/usr/local:/opt \
        -DPROJ_INCLUDE_DIR=/usr/local/proj4/include \
        -DPROJ_LIBRARY=/usr/local/proj4/lib/libproj.so \
        -DWITH_FCGI=0 \
        -DWITH_PROTOBUFC=0 \
        -DWITH_HARFBUZZ=0 \
        -DWITH_CAIRO=0 \
        -DWITH_FRIBIDI=0 \
		-DWITH_PROJ=ON \
		-DWITH_SOS=ON \
		-DWITH_WMS=ON \
		-DWITH_GEOS=ON \
		-DWITH_GDAL=ON \
		-DWITH_OGR=ON \
		-DWITH_CURL=ON \
		-DWITH_CLIENT_WFS=ON \
		-DWITH_CLIENT_WMS=ON \
		-DWITH_WFS=ON \
		-DWITH_WCS=ON \
		-DWITH_PHP=ON \
        ../ >../configure.out.txt \
    && make \
    && make install \
    && echo extension=php_mapscript.so> /etc/php/5.6/apache2/conf.d/20-mapscript.ini \
    && echo extension=php_mapscript.so> /etc/php/5.6/cli/conf.d/20-mapscript.ini


RUN apt purge wget -y \
  && cd /mapserv-work \
  && rm mapserver-7.2.0.tar.gz \
  && rm proj-4.9.3.tar.gz

# Expose Apache's default HTTP port
EXPOSE 80

# Start Apache in the foreground
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]

