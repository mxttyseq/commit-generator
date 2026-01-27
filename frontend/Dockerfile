FROM nginx:alpine

# smažeme default config
RUN rm /etc/nginx/conf.d/default.conf

# vlastní config
COPY nginx.conf /etc/nginx/conf.d/default.conf

# frontend soubory
COPY . /usr/share/nginx/html

EXPOSE 1234
