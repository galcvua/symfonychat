# Chat Application with Symfony 7, API Platform 4, Mercure and Firebase WebPush

Demo: [https://chat.symfonystudio.com](https://chat.symfonystudio.com)  

Some popular messaging platforms have faced criticism for their involvement in the spread of propaganda and illegal activities. In response, governments have blocked access to these platforms, arresting representatives and even owners.  
Your business shouldn't be affected by these disruptions.  

Explore this simple example to learn how to integrate communication features into your Symfony project.  

## Installation  

1. Clone the repository and install dependencies.  
2. Set the `DATABASE_URL` in `.env.local` and run migrations.  
3. Obtain keys for Google user authentication from [Google Cloud Console](https://console.cloud.google.com/apis/credentials) and insert the values for `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` in `.env.local`.  
4. Install the Mercure Hub. More details at [Mercure Hub Installation](https://mercure.rocks/docs/hub/install).  
   Mercure is an external service that Symfony integrates with, but unlike other external services (e.g., MySQL or Redis), it must be publicly accessible. Don't forget to set the `MERCURE_PUBLIC_URL` value in `.env.local`.  
5. In the hub settings, set `publisher_jwt` to match the value of `MERCURE_JWT_SECRET` in `.env.local`.  
6. Additionally, add `subscriber_jwks_url https://www.googleapis.com/oauth2/v3/certs` in the Mercure Hub settings, or allow anonymous subscription (not recommended).

## WebPush

To send notifications even when your application is offline, you can integrate Firebase Cloud Messaging (FCM). Start by creating a project in the [Firebase console](https://console.firebase.google.com/), then add an app, and create a private key for a service account. Place the private key (in JSON format) in the `GOOGLE_APPLICATION_CREDENTIALS` variable in your `.env.local` file.

If you'd like to pass additional FCM configuration settings to your frontend via the API, you can also add the client configuration and `VAPID_KEY`.

In a production environment, ensure asynchronous push notifications are enabled by running:

```bash
php bin/console messenger:consume webpush
```

To see web push notifications in action on our demo page, enable notifications when prompted by your browser.

## Testing

All API endpoints are covered by integration tests. To run these tests, add `DATABASE_URL` to your `.env.test.local` file. The name of the test database will automatically include the _test suffix. Make sure your database user has the necessary permissions to create databases.

Connections to external services like Google and the Mercure Hub are not required for testing, as these services are mocked in the test environment.

## Usage  

1. Start your development server and navigate to [`/api`](https://chat.symfonystudio.com/api) to see the OpenAPI documentation.  
2. Use a Bearer token with a valid Google authentication token for authorization and to perform test requests.  
3. You can now integrate this API into your project to enhance communication features. See the demo at [https://chat.symfonystudio.com](https://chat.symfonystudio.com).  

## License  

This project is licensed under the MIT License.  
