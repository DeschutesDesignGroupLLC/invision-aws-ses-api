# AWS Simple Email Service for Invision Power Board


Introducing our powerful yet user-friendly application that revolutionizes email sending on the Invision Power Board platform by leveraging the AWS Simple Email Service API. With this lightweight solution, you can effortlessly send emails, supercharging your communication capabilities.

By seamlessly integrating with your existing email delivery methods, our application overrides default settings with ease. With just a simple click, you can enable or disable the service, giving you complete control.

But that's not all - our application goes beyond basic functionality. It incorporates advanced complaint and bounce management actions that can be applied to members, significantly improving email throughput and deliverability. Say goodbye to email delivery headaches and hello to seamless communication.

Harnessing the power of Amazon SES, one of the most robust and cost-effective transactional email service providers available, our application ensures top-notch performance. While the setup process is lightweight and straightforward, the majority of the configuration takes place within the AWS Console, where you can fine-tune your email sending preferences for optimal results.

Please note that when you initially begin using AWS Simple Email Service, you'll operate within a non-production sandbox environment. This allows you to test the service without impacting your reputation or deliverability. When you're ready to transition into production, you can request access from AWS directly through the Sending Statistics section. The process is straightforward, and we'll be there to guide you every step of the way.

Elevate your email sending capabilities to new heights with our application, and experience the efficiency and reliability that comes with AWS-powered communication. Don't miss out on this opportunity to enhance your email delivery and take your communication game to the next level.

**Support Topic:** Click [here](https://invisioncommunity.com/forums/topic/463340-aws-simple-email-service-with-bounce-management/) to read the Invision Power Board support topic.

**[View AWS Simple Email Service Pricing](https://aws.amazon.com/ses/pricing/)**

**[Community Forums](https://community.deschutesdesigngroup.com)**

**[Support](https://www.deschutesdesigngroup.com/support)**

**Simple Email Service Installation For Sending Emails:**

1. [Create or Sign In](https://console.aws.amazon.com/) to your AWS Console.
2. Proceed to the IAM section of the AWS Console.
3. On the left, select the Users section and add a new user. If you already have a user you would like to use, proceed to the next step.
4. Enable programmatic access for the user account.
5. Attach the AmazonSESFullAccess policy to the user account.
6. Finish creating the account, adding any tags you'd like for easy identification.
7. Copy and paste your Access Key and Secret Key into the corresponding fields within the Invision Power Board ACP settings.
8. Proceed to the AWS Simple Email Service section of the AWS console.
9. Select your closest region in the top right corner.
10. Copy the corresponding region identifier and input it into the corresponding field within the Invision Power Board ACP settings.
11. Save your Invision Power Board ACP settings.
12. Back within the AWS Console, select Domains -> Verify a New Domain to add the domain you wish to send emails from. This is called Verified Identities - Add Identity, if you are using the new AWS console.
13. You may enter as many email addresses as you wish to send email from.
14. Make sure to check the "Generate DKIM Settings" checkbox. These are enabled by default if using the new AWS console. This will help with your deliverability.
15. Update your DNS records for the domain you are verifying by adding the TXT and CNAME records that are presented.
16. Make sure to not update the MX records as this will affect your ability to receive the emails. We are only configuring sending emails.
17. For each verified identity you add, enter the same email address in the corresponding field of your Invision Power Board ACP settings.
18. Once an email address has been verified, you are ready to start sending emails.
19. Fill in the Default Sending Email Address field of your Invision Power Board ACP settings. This will help your Invision Power Board website not send emails from unverified domains. This may happen through third party applications.
20. While in the sandbox environment, you will need to add your test receiving email address under the Email Addresses section or else you will receive a sending error.
21. Once everything is configured, proceed back to the Invision Power Board ACP and proceed to Email Settings. You can use the built in Test Email Settings feature to test AWS SES. You will see the corresponding logs under the AWS SES application and any generated errors.

**Simple Notification Service Installation For Handling Bounces/Complaints:**

1. [Create or Sign In](https://console.aws.amazon.com/) to your AWS Console.
2. Proceed to the IAM section of the AWS Console.
3. Select the user that you created/or designated when setting up Simple Email Service for sending emails.
4. Attach the AmazonSNSFullAccess policy to the user account.
5. Proceed to the AWS Simple Notification Service section of the AWS console.
6. Create two new topics for handling bounce and complaint notifications, one for bounces and one for complaints. Make sure these are Standard topics. Name them for easy identification.
7. Proceed to the AWS Simple Email Service section of the AWS console and click on your Verified Identity/Domain used in sending emails.
8. Click the Notifications tab and edit the Feedback Notifications. Select your newly created SNS topics for their respective field. Make sure to "Include original email headers".
9. Once your domain notifications are set, proceed back to your Invision Power Board ACP settings and proceed to the REST & OAuth section.
10. Create a new API key and make sure to allow access to all endpoints under the AWS Simple Email Service application. Enable logging for both endpoints as well.
11. Once you have created the new API key, click on the API reference tab and copy the example API POST URL for each AWS Simple Email Service endpoint; bounces, and complaints.
12. Proceed back to the AWS Simple Notification Service and create a new subscription. You will create two subscriptions as well, one for bounces and one for complaints.
13. Choose the matching topic and select HTTPS for the protocol. Paste the appropriate API POST URL you copied from step 11 and attach the ?key=APIKEY query parameter to the end of the URL. For example, the entire URL should look something like: https://community.deschutesdesigngroup.com/api/awsses/bounces?key=APIKEY. If you do not utilize Friendly URL's (the default setting), you may have "index.php" in your URL. Make sure "Enable raw message delivery" is not checked.
14. Make sure to create a second subscription for complaints - you should have two subscriptions, one for bounces with the bounce API URL and one for complaints with the complaints API URL. 
15. The AWS Simple Email Service application will take care of confirming the subscription for you. If the subscription does not show Confirmed, your API endpoint may not be configured correctly and you should submit a support ticket for assistance. 
16. AWS should now post a notification every time a bounce and complaint is encountered and Invision Power Board will process the notifications based on the email address using the Bounce and Complaint settings saved in your ACP settings. 

Bounce and Complaint Notifications can be tested by sending a test email through Invision Power Board's Test Email function to **bounce@simulator.amazonses.com** and **complaint@simulator.amazonses.com**. Logs for each notification should be seen in your settings. 
