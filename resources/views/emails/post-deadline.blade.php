<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Job Deadline Passed - {{ $app_name }}</title>
    <style>
        @media only screen and (max-width: 600px) {
            .container {
                width: 100% !important;
                padding: 20px !important;
            }

            .button {
                display: block !important;
                width: 100% !important;
                text-align: center !important;
            }
        }
    </style>
</head>

<body
    style="font-family: Arial, Helvetica, sans-serif; background-color: #f8fafc; color: #334155; margin: 0; padding: 0;">
    <div
        style="max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow: hidden;">

        <!-- Header -->
        <div
            style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); padding: 40px 30px; text-align: center; color: white;">
            <h1 style="font-size: 26px; font-weight: 700; margin-bottom: 8px;">⚠️ Job Deadline Passed</h1>
            <p style="font-size: 16px; opacity: 0.9;">Time to review applications for "{{ $vacancy_title }}"</p>
        </div>

        <!-- Body -->
        <div style="padding: 40px 30px;">
            <!-- Greeting -->
            <div style="margin-bottom: 30px;">
                <h2 style="color: #1e293b; margin-bottom: 16px;">Hello {{ $employer_name }},</h2>
                <p style="color: #64748b; line-height: 1.6;">
                    The application deadline for your job posting <strong>"{{ $vacancy_title }}"</strong>
                    has passed on <strong>{{ $deadline_date }}</strong>.
                    It's time to review the applications you've received and proceed with the next steps.
                </p>
            </div>

            <!-- Summary Card -->
            <div
                style="background: #fffbeb; border: 1px solid #fcd34d; border-radius: 12px; padding: 25px; margin: 25px 0;">
                <h3 style="color: #92400e; margin-bottom: 20px; text-align: center;">📋 Job Summary</h3>

                <div style="display: grid; grid-template-columns: 1fr; gap: 15px;">
                    <div>
                        <div style="color: #92400e; font-size: 14px; margin-bottom: 5px;">Job Title</div>
                        <div
                            style="font-weight: 600; color: #1e293b; padding: 10px; background: white; border-radius: 8px; border: 1px solid #fcd34d;">
                            {{ $vacancy_title }}
                        </div>
                    </div>

                    <div>
                        <div style="color: #92400e; font-size: 14px; margin-bottom: 5px;">Deadline Date</div>
                        <div
                            style="font-weight: 600; color: #1e293b; padding: 10px; background: white; border-radius: 8px; border: 1px solid #fcd34d;">
                            {{ $deadline_date }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Required -->
            <div
                style="background: #fef3c7; border: 1px solid #fcd34d; border-radius: 8px; padding: 20px; margin: 20px 0;">
                <div style="display: flex; align-items: flex-start;">
                    <div style="font-size: 20px; margin-right: 12px;">📢</div>
                    <div>
                        <strong style="color: #92400e;">Action Required:</strong><br>
                        <span style="color: #92400e; font-size: 14px;">
                            Please review all applications and update the job status (e.g., "Under Review", "Closed",
                            "Filled")
                            in your dashboard to keep candidates informed.
                        </span>
                    </div>
                </div>
            </div>

            <!-- Next Steps -->
            <div
                style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; padding: 25px; margin: 25px 0;">
                <h3 style="color: #0369a1; margin-bottom: 20px;">✅ Recommended Next Steps</h3>

                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <div style="display: flex; align-items: flex-start;">
                        <div
                            style="background: #10b981; color: white; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 12px; flex-shrink: 0;">
                            1
                        </div>
                        <div>
                            <strong>Review Applications</strong><br>
                            <span style="color: #64748b; font-size: 14px;">Check all submitted applications in your
                                dashboard</span>
                        </div>
                    </div>

                    <div style="display: flex; align-items: flex-start;">
                        <div
                            style="background: #10b981; color: white; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 12px; flex-shrink: 0;">
                            2
                        </div>
                        <div>
                            <strong>Shortlist Candidates</strong><br>
                            <span style="color: #64748b; font-size: 14px;">Select qualified candidates for
                                interviews</span>
                        </div>
                    </div>

                    <div style="display: flex; align-items: flex-start;">
                        <div
                            style="background: #10b981; color: white; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 12px; flex-shrink: 0;">
                            3
                        </div>
                        <div>
                            <strong>Update Job Status</strong><br>
                            <span style="color: #64748b; font-size: 14px;">Change the job status to keep applicants
                                informed</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $dashboard_url }}"
                    style="display: inline-block; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; text-decoration: none; padding: 14px 28px; border-radius: 10px; font-weight: 600; font-size: 16px; margin: 0 10px 15px 10px; min-width: 200px;">
                    📊 Go to Dashboard
                </a>
            </div>

            <!-- Reminder -->
            <div style="text-align: center; padding: 20px; background: #f8fafc; border-radius: 8px; margin: 20px 0;">
                <p style="color: #64748b; margin: 0; font-size: 14px;">
                    <strong>Tip:</strong> Prompt communication with applicants helps maintain a positive employer brand
                    and improves your chances of hiring the best candidates.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div style="background: #f1f5f9; padding: 30px; text-align: center; border-top: 1px solid #e2e8f0;">
            <div style="margin-bottom: 15px;">
                <img src="https://yorpnyc.org.ph/images/clark-dark.png" alt="{{ $app_name }}"
                    style="height: 40px; width: auto; opacity: 0.8;">
            </div>

            <p style="color: #64748b; margin-bottom: 8px; font-size: 14px;">
                This is an automated reminder from {{ $app_name }}
            </p>

            <p style="font-size: 13px; color: #94a3b8; margin-top: 10px;">
                © {{ $current_year }} {{ $app_name }}. All rights reserved.<br>
                If you have any questions, contact us at
                <a href="mailto:{{ $support_email }}" style="color: #3b82f6; text-decoration: none;">
                    {{ $support_email }}
                </a>
            </p>

            <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #e2e8f0;">
                <p style="font-size: 12px; color: #94a3b8;">
                    You received this email because you posted a job on {{ $app_name }}.<br>
                    To manage your email preferences, visit your account settings.
                </p>
            </div>
        </div>
    </div>
</body>

</html>
