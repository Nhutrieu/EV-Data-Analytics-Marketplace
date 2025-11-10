FROM node:20-alpine

WORKDIR /app

COPY package*.json ./
RUN npm install --production

COPY . .

# Expose port 8001
EXPOSE 8001

CMD ["node", "index.js"]
