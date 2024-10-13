# Chat on Symfony 7, API Platform 4, Mercure

Demo: [https://chat.symfonystudio.com](https://chat.symfonystudio.com)

Popular messengers try to profit from propaganda and crime. In response, governments block them, arrest representatives, and even owners.
Your business should not become a hostage to this confrontation.
Explore this simple example to understand how to add communication to your own project on Symfony.

## Installation

1. Clone the repository and install dependencies.
2. Set the `DATABASE_URL` in `.env.local` and run migrations.
3. Obtain keys for Google user authorization from [Google Cloud Console](https://console.cloud.google.com/apis/credentials) and insert the values for `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` in `.env.local`.
4. Install Mercure Hub. More details at [Mercure Hub Installation](https://mercure.rocks/docs/hub/install). This service is similar to others we use in Symfony, with one exception: it must be accessible on an external interface. Therefore, don't forget to set the `MERCURE_PUBLIC_URL` value in `.env.local`.
5. In the hub settings, specify the same value for `publisher_jwt` as `MERCURE_JWT_SECRET` in `.env.local`.
6. Additionally, add `subscriber_jwks_url https://www.googleapis.com/oauth2/v3/certs` in the Mercure Hub settings, or allow anonymous subscription (not recommended).

## Usage

1. Start your development server and navigate to `/api` to see the OpenAPI documentation.
2. Use a Bearer with a valid Google token for authorization and to perform test requests.
3. Now you can use this API in your project. For an example, see the demo at [https://chat.symfonystudio.com](https://chat.symfonystudio.com).

## License

This project is licensed under the MIT License.
