<template>
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="closeModal">
        <div class="bg-white rounded-lg max-w-sm w-full mx-4" @click.stop>
            <!-- Header -->
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Newsletter Signup</h3>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Zoho Form Container -->
            <div class="p-4">
                <!-- Zoho Campaigns Web-Optin Form -->
                <div id="sf3z64bd6d6e23993fa70b064e6d0607901c0f3a03a5b16d09df2b462bb40db71104" data-type="signupform" style="opacity: 1;">
                    <div id="customForm">
                        <div name="SIGNUP_BODY" changeitem="BG_IMAGE" style="width: 300px; height: 380px; position: relative; margin: auto; background-color: rgb(255, 255, 255); overflow: hidden">
                            <div changeitem="ELEGANTFORM_IMAGE" style="width: 100%; height: 100%; position: absolute; bottom: 0">
                                <img src="https://campaign-image.com/zohocampaigns/1301d85c_sign_form_bg_41.png" style="width: 100%; height: 100%; position: relative">
                            </div>
                            <div style="width: 300px; height: 380px; position: relative; font-family: Arial; margin: auto">
                                <div style="position:relative;">
                                    <div id="Zc_SignupSuccess" style="display:none;position:absolute;margin-left:4%;width:90%;background-color: white; padding: 3px; border: 3px solid rgb(194, 225, 154);  margin-top: 10px;margin-bottom:10px;word-break:break-all">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tbody>
                                                <tr>
                                                    <td width="10%">
                                                        <img class="successicon" src="https://zawj-cmpzourl.maillist-manage.com/images/challangeiconenable.jpg" align="absmiddle">
                                                    </td>
                                                    <td>
                                                        <span id="signupSuccessMsg" style="color: rgb(73, 140, 132); font-family: sans-serif; font-size: 14px;word-break:break-word">&nbsp;&nbsp;Thank you for Signing Up</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <form method="POST" id="zcampaignOptinForm" style="margin: 0px; width: 100%; color: rgb(255, 255, 255)" action="https://zawj-cmpzourl.maillist-manage.com/weboptin.zc" target="_zcSignup">
                                    <div style="background-color: rgb(255, 235, 232); padding: 10px; color: rgb(210, 0, 0); font-size: 11px; border: 1px solid rgb(255, 217, 211); opacity: 1; position: absolute; width: 80%; margin: 20px 10%; box-shadow: rgb(27, 27, 27) 0px 5px 12px 0px; display: none;" id="errorMsgDiv">Please correct the marked field(s) below.</div>
                                    <div style="text-align: center; width: 100%; float: left; position: absolute; z-index: 2; bottom: 75px">
                                        <div style="font-size: 22px; font-weight: 500; font-family: Poppins, Arial; line-height: 1.556; margin: 0; color: rgb(106, 73, 162); width: 100%; float: left; text-align: center">Stay up-to-date!</div>
                                        <div style="font-size: 18px; color: rgb(72, 72, 72); line-height: 1.5; width: 100%; float: left; margin: 10px 0; font-weight: 400; font-family: Roboto, Arial;">Get insights and tips from experts</div>
                                        <div style="text-align: center; margin: 20px 0; width: 210px; height: 35px; margin: auto; margin-bottom: 10px; display: inline-block">
                                            <div id="Zc_SignupSuccess2" style="position: absolute; width: 87%; background-color: white; padding: 3px; border: 3px solid rgb(194, 225, 154); word-break: break-all; opacity: 1; display: none">
                                                <div style="width: 20px; padding: 5px; display: table-cell">
                                                    <img class="successicon" src="https://campaigns.zoho.com/images/challangeiconenable.jpg" style="width: 20px">
                                                </div>
                                                <div style="display: table-cell">
                                                    <span style="color: rgb(73, 140, 132); font-family: sans-serif; font-size: 14px; line-height: 30px; display: block">Thank you!</span>
                                                </div>
                                            </div>
                                            <input type="text" placeholder="Email Address" changeitem="SIGNUP_FORM_FIELD" name="CONTACT_EMAIL" id="EMBED_FORM_EMAIL_LABEL" style="border-width: 0 0 1px; border: 1px solid rgb(106, 73, 162); background-color: rgb(240, 240, 240); border-style: solid; width: 100%; height: 100%; z-index: 4; outline: none; padding: 5px 5px; box-sizing: border-box; color: rgb(136, 136, 136); font-family: Poppins, Arial; font-size: 12px">
                                        </div>
                                        <div style="position: relative; width: 210px; height: 35px; display: inline-block">
                                            <input type="button" style="text-align: center; border-radius: 0px; background-color: rgb(106, 73, 162); width: 100%; height: 100%; z-index: 5; border: 0; color: rgb(255, 255, 255); cursor: pointer; outline: none; font-family: Arial; font-size: 14px" name="SIGNUP_SUBMIT_BUTTON" id="zcWebOptin" value="SIGN UP" @click="handleSignup">
                                        </div>
                                    </div>
                                    <input type="hidden" id="fieldBorder" value="">
                                    <input type="hidden" id="submitType" name="submitType" value="optinCustomView">
                                    <input type="hidden" id="emailReportId" name="emailReportId" value="">
                                    <input type="hidden" id="formType" name="formType" value="QuickForm">
                                    <input type="hidden" name="zx" id="cmpZuid" value="134fcff32">
                                    <input type="hidden" name="zcvers" value="3.0">
                                    <input type="hidden" name="oldListIds" id="allCheckedListIds" value="">
                                    <input type="hidden" id="mode" name="mode" value="OptinCreateView">
                                    <input type="hidden" id="zcld" name="zcld" value="1146877ccd6f81ffc">
                                    <input type="hidden" id="zctd" name="zctd" value="1146877ccd6f787a9">
                                    <input type="hidden" id="document_domain" value="">
                                    <input type="hidden" id="zc_Url" value="zawj-cmpzourl.maillist-manage.com">
                                    <input type="hidden" id="new_optin_response_in" value="0">
                                    <input type="hidden" id="duplicate_optin_response_in" value="0">
                                    <input type="hidden" name="zc_trackCode" id="zc_trackCode" value="ZCFORMVIEW">
                                    <input type="hidden" id="zc_formIx" name="zc_formIx" value="3z64bd6d6e23993fa70b064e6d0607901c0f3a03a5b16d09df2b462bb40db71104">
                                    <input type="hidden" id="viewFrom" value="URL_ACTION">
                                    <span style="display: none" id="dt_CONTACT_EMAIL">1,true,6,Contact Email,2</span>
                                    <span style="display: none" id="dt_FIRSTNAME">1,false,1,First Name,2</span>
                                    <span style="display: none" id="dt_LASTNAME">1,false,1,Last Name,2</span>
                                </form>
                            </div>
                        </div>
                        <div style="display: none" id="unauthPageTitle">zawajAfrica - Form</div>
                    </div>
                    <img src="https://zawj-cmpzourl.maillist-manage.com/images/spacer.gif" id="refImage" onload="referenceSetter(this)" style="display:none;">
                </div>
                <input type="hidden" id="signupFormType" value="QuickForm_Vertical">
                <div id="zcOptinOverLay" oncontextmenu="return false" style="display:none;text-align: center; background-color: rgb(0, 0, 0); opacity: 0.5; z-index: 100; position: fixed; width: 100%; top: 0px; left: 0px; height: 988px;"></div>
                <div id="zcOptinSuccessPopup" style="display:none;z-index: 9999;width: 800px; height: 40%;top: 84px;position: fixed; left: 26%;background-color: #FFFFFF;border-color: #E6E6E6; border-style: solid; border-width: 1px;  box-shadow: 0 1px 10px #424242;padding: 35px;">
                    <span style="position: absolute;top: -16px;right:-14px;z-index:99999;cursor: pointer;" id="closeSuccess">
                        <img src="https://zawj-cmpzourl.maillist-manage.com/images/videoclose.png">
                    </span>
                    <div id="zcOptinSuccessPanel"></div>
                </div>
            </div>
            
            <!-- Skip button -->
            <div class="p-4 text-center border-t">
                <button @click="skipOptin" class="text-gray-500 hover:text-gray-700 text-sm">
                    Maybe later
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ZohoOptinModal',
    props: {
        show: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            scriptLoaded: false,
            formInitialized: false
        }
    },
    watch: {
        show(newVal) {
            if (newVal && !this.scriptLoaded) {
                this.loadZohoAssets()
            }
        }
    },
    mounted() {
        if (this.show) {
            this.loadZohoAssets()
        }
    },
    methods: {
        closeModal() {
            this.$emit('close')
        },
        skipOptin() {
            localStorage.setItem('zoho_optin_skipped', 'true')
            this.closeModal()
        },
        handleSignup() {
            console.log('Signup button clicked')
            
            const emailInput = document.getElementById('EMBED_FORM_EMAIL_LABEL')
            const email = emailInput ? emailInput.value : ''
            
            if (!email || !this.isValidEmail(email)) {
                this.showError('Please enter a valid email address')
                return
            }
            
            // Try to use Zoho's function if available
            if (typeof window.zcWebOptin === 'function') {
                console.log('Using Zoho zcWebOptin function')
                window.zcWebOptin()
            } else {
                // Fallback: submit form manually
                console.log('Submitting form manually')
                this.submitFormManually(email)
            }
        },
        isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
            return emailRegex.test(email)
        },
        showError(message) {
            const errorDiv = document.getElementById('errorMsgDiv')
            if (errorDiv) {
                errorDiv.textContent = message
                errorDiv.style.display = 'block'
                setTimeout(() => {
                    errorDiv.style.display = 'none'
                }, 3000)
            }
        },
        submitFormManually(email) {
            // Create a form and submit it manually
            const form = document.createElement('form')
            form.method = 'POST'
            form.action = 'https://zawj-cmpzourl.maillist-manage.com/weboptin.zc'
            form.target = '_blank'
            
            // Add form fields
            const fields = {
                'CONTACT_EMAIL': email,
                'submitType': 'optinCustomView',
                'formType': 'QuickForm',
                'zx': '134fcff32',
                'zcvers': '3.0',
                'mode': 'OptinCreateView',
                'zcld': '1146877ccd6f81ffc',
                'zctd': '1146877ccd6f787a9',
                'zc_Url': 'zawj-cmpzourl.maillist-manage.com',
                'zc_trackCode': 'ZCFORMVIEW',
                'zc_formIx': '3z64bd6d6e23993fa70b064e6d0607901c0f3a03a5b16d09df2b462bb40db71104',
                'viewFrom': 'URL_ACTION'
            }
            
            Object.keys(fields).forEach(key => {
                const input = document.createElement('input')
                input.type = 'hidden'
                input.name = key
                input.value = fields[key]
                form.appendChild(input)
            })
            
            document.body.appendChild(form)
            form.submit()
            document.body.removeChild(form)
            
            // Show success message
            this.showSuccess()
        },
        showSuccess() {
            const successDiv = document.getElementById('Zc_SignupSuccess2')
            if (successDiv) {
                successDiv.style.display = 'block'
                setTimeout(() => {
                    this.closeModal()
                }, 2000)
            } else {
                // Fallback success
                alert('Thank you for signing up!')
                this.closeModal()
            }
        },
        loadZohoAssets() {
            if (this.scriptLoaded) {
                return
            }
            
            console.log('Loading Zoho assets...')
            
            // Add CSS styles
            const style = document.createElement('style')
            style.innerHTML = '::-webkit-input-placeholder { color: rgb(106, 73, 162) }'
            document.head.appendChild(style)

            // Load Zoho Campaign script
            const script = document.createElement('script')
            script.type = 'text/javascript'
            script.src = 'https://zawj-cmpzourl.maillist-manage.com/js/optin.min.js'
            script.onload = () => {
                console.log('Zoho script loaded successfully')
                this.scriptLoaded = true
                
                // Wait a bit for the script to initialize
                setTimeout(() => {
                    this.initializeZohoForm()
                }, 500)
            }
            script.onerror = () => {
                console.error('Failed to load Zoho script')
                this.scriptLoaded = true // Still mark as loaded to prevent retry
            }
            document.head.appendChild(script)

            // Add the form submit handler
            window.runOnFormSubmit_sf3z64bd6d6e23993fa70b064e6d0607901c0f3a03a5b16d09df2b462bb40db71104 = (th) => {
                console.log('Zoho form submitted successfully via callback')
                this.showSuccess()
            }
        },
        initializeZohoForm() {
            try {
                if (typeof setupSF === 'function') {
                    console.log('Initializing Zoho form with setupSF')
                    setupSF('sf3z64bd6d6e23993fa70b064e6d0607901c0f3a03a5b16d09df2b462bb40db71104','ZCFORMVIEW',false,'light',false,'0')
                    this.formInitialized = true
                } else {
                    console.warn('setupSF function not available')
                }
            } catch (error) {
                console.error('Error initializing Zoho form:', error)
            }
        }
    }
}
</script>

<style scoped>
/* Ensure proper z-index for modal */
.fixed {
    z-index: 1000;
}
</style> 