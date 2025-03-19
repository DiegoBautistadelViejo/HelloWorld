# Web App Deployment with Azure and GitHub Actions

This repository contains the code and configuration to deploy a simple PHP web application on Azure App Service with GitHub Actions for CI/CD. The application uses a `GREETING_NAME` variable to display a personalized message on a web page. 

The deployment process uses GitHub Actions to automate the process of deploying the app to Azure when changes are pushed to the `main` or `llm-chat` branch.

## Table of Contents

1. [Project Overview](#project-overview)
2. [Components](#components)
3. [Setup Guide](#setup-guide)
    1. [Azure Setup](#azure-setup)
    2. [GitHub Setup](#github-setup)
4. [Deployment Process](#deployment-process)
5. [Usage](#usage)
6. [Pipeline Details](#pipeline-details)
7. [Optional Features](#optional-features)
8. [Troubleshooting](#troubleshooting)

## Project Overview

This project deploys a PHP web application on Azure App Service. The application reads a `GREETING_NAME` variable from the GitHub repository and displays it on a simple web page. The deployment process is automated using GitHub Actions, allowing for continuous integration and continuous deployment (CI/CD).

## Components

1. **PHP Web Application**: A basic web app with PHP that displays a greeting message using the `GREETING_NAME` environment variable from GitHub.
2. **Azure App Service**: Azure's platform-as-a-service (PaaS) for hosting the web application.
3. **GitHub Actions CI/CD Pipeline**: Automated deployment process that deploys the app to Azure when changes are pushed to the `main` or `llm-chat` branch.
4. **Environment Variables**: The application uses the `GREETING_NAME` environment variable to personalize the greeting message.

## Setup Guide

### Azure Setup

To deploy the app on Azure, you need to create an Azure App Service. Follow these steps:

1. **Create an Azure Account**:
   - If you don’t have one, sign up at [Azure](https://azure.microsoft.com/en-us/free/).
   
2. **Create a New App Service**:
   - Go to the Azure Portal.
   - Navigate to `App Services` and click `+ Create`.
   - Choose **PHP** as the runtime stack.
   - Select a region and create your App Service plan (free or basic, depending on your needs).
   - After the app service is created, note down the **App Name** (this is used in the deployment pipeline).

3. **Create Azure Service Principal**:
   - Use the Azure CLI to create a service principal with the necessary permissions to deploy to Azure:

     ```bash
     az ad sp create-for-rbac --name "github-deployment" --role contributor --scopes /subscriptions/YOUR_SUBSCRIPTION_ID --sdk-auth
     ```

   - Store the JSON output from this command, which contains the credentials needed to authenticate from GitHub.

### GitHub Setup

1. **Clone the Repository**:
   - Clone this repository to your local machine.

     ```bash
     git clone https://github.com/DiegoBautistadelViejo/HelloWorld.git
     cd HelloWorld
     ```

2. **Set Up GitHub Secrets**:
   - Go to your GitHub repository page.
   - Navigate to `Settings` -> `Secrets and variables` -> `Actions`.
   - Add the following secrets:
     - `AZURE_CREDENTIALS`: The JSON output from the Azure service principal creation step.
     - `GREETING_NAME`: The greeting message or name you want to display.

3. **Create and Push Changes**:
   - Create a new branch for the deployment (optional):
     
     ```bash
     git checkout -b deploy-to-azure
     ```

   - Push the code to GitHub:

     ```bash
     git add .
     git commit -m "Set up deployment pipeline"
     git push origin deploy-to-azure
     ```

## Deployment Process

1. **CI/CD Pipeline**:
   - The pipeline is configured using GitHub Actions.
   - When you push changes to the `main` or `llm-chat` branch, GitHub Actions automatically triggers the pipeline and deploys the application to Azure App Service.

2. **Deployment Steps**:
   - **Checkout code** from GitHub.
   - **Set up PHP** runtime on the GitHub Actions runner.
   - **Login to Azure** using the Azure service principal credentials stored as a secret.
   - **Deploy the code** to the Azure App Service.

## Usage

Once the application is deployed, you can access it via the Azure-provided URL (e.g., `https://your-app-name.azurewebsites.net`).

- Open the URL in a browser.
- The page will display a greeting message based on the `GREETING_NAME` variable set in GitHub Secrets.

## Pipeline Details

The `.github/workflows/deploy.yml` file contains the configuration for the GitHub Actions CI/CD pipeline. It defines the following jobs:

- **Checkout code**: Pulls the latest code from the repository.
- **Set up PHP**: Configures the runner with PHP 8.3.
- **Login to Azure**: Uses the Azure service principal credentials to authenticate.
- **Deploy to Azure**: Deploys the PHP app to Azure App Service.

Here's an example of the pipeline configuration:

```yaml
name: Azure Web App Deployment

on:
  push:
    branches:
      - main

jobs:
  build:
    runs-on: ubuntu-latest

    steps:
    - name: Checkout code
      uses: actions/checkout@v2

    - name: Set up PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.3'

    - name: Log in to Azure
      uses: azure/login@v1
      with:
        creds: ${{ secrets.AZURE_CREDENTIALS }}

    - name: Deploy to Azure Web App
      uses: azure/webapps-deploy@v2
      with:
        app-name: hello-world-app
        slot-name: production
        package: .
```

## Optional Features

### PHP Info Page

Once deployed, you can create a PHP info page to check your PHP configuration:

1. For this, you need the `phpinfo.php` in the root of your app:

    ```php
    <?php
    phpinfo();
    ?>
    ```

2. Visit the `phpinfo.php` page at your app's URL (`https://your-app-name.azurewebsites.net/phpinfo.php`) to view detailed information about the PHP environment.

## Troubleshooting

- **Deployment Issues**: If the deployment fails, check the GitHub Actions logs for more detailed error messages.
- **Environment Variables**: Ensure that `AZURE_CREDENTIALS` and `OPENAI_API_KEY` are set correctly in GitHub Secrets.
