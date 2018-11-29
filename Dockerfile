FROM node:11-alpine
ENV CHROME_BIN="/usr/bin/chromium-browser" \
    PUPPETEER_SKIP_CHROMIUM_DOWNLOAD="true" \
    NODE_ENV="production"
RUN set -x \
      && apk update \
      && apk upgrade \
      # Replacing default repositories with edge one, so we can download needed version
      && echo "http://dl-cdn.alpinelinux.org/alpine/edge/testing" > /etc/apk/repositories \
      && echo "http://dl-cdn.alpinelinux.org/alpine/edge/community" >> /etc/apk/repositories \
      && echo "http://dl-cdn.alpinelinux.org/alpine/edge/main" >> /etc/apk/repositories \
      \
      # Add the packages
      && apk add --no-cache dumb-init curl make gcc g++ python linux-headers binutils-gold gnupg libstdc++ nss chromium php7 php7-fileinfo php7-mbstring composer \
      \
      && npm install -g puppeteer@0.13.0 \
      \
      # Do some cleanup
      && apk del --no-cache make gcc g++ python binutils-gold gnupg libstdc++ \
      && rm -rf /usr/include \
      && rm -rf /var/cache/apk/* /root/.node-gyp /usr/share/man /tmp/* \
      && echo
COPY . ./app
WORKDIR /app
RUN npm install
RUN composer install
EXPOSE 3000
ENTRYPOINT ["/usr/bin/dumb-init"]
CMD ["php", "-S", "0.0.0.0:3000", "-t", "/app/docker-files/src"]
