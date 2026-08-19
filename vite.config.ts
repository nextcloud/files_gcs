import { createAppConfig } from "@nextcloud/vite-config";
import { join, resolve } from "path";

export default createAppConfig(
  {
	  settings: resolve(join('src', 'settings.ts')),
  },
  {
    createEmptyCSSEntryPoints: true,
    extractLicenseInformation: true,
    thirdPartyLicense: false,
	inlineCSS: {
		relativeCSSInjection: true,
	}
  }
);
