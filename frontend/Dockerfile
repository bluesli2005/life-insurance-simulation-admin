FROM node:14.21.3-buster AS build
WORKDIR /frontend
COPY package*.json ./
RUN npm ci
COPY . .
ARG MIX_API_BASE_URL=
ENV MIX_API_BASE_URL=$MIX_API_BASE_URL
RUN npm run production

FROM nginx:1.26-alpine
COPY --from=build /frontend/dist /usr/share/nginx/html
COPY nginx.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
