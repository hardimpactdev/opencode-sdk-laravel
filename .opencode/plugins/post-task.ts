import type { Plugin } from "@opencode-ai/plugin"
import type { OpencodeClient } from "@opencode-ai/sdk"

function prompt(client: OpencodeClient, sessionID: string, text: string) {
  return client.session.prompt({
    path: { id: sessionID },
    body: {
      parts: [{ type: "text", text }],
    },
  })
}

function command(client: OpencodeClient, sessionID: string, name: string, args = "") {
  return client.session.command({
    path: { id: sessionID },
    body: {
      command: name,
      arguments: args,
    },
  })
}

export const PostTaskPlugin: Plugin = async ({ client }) => {
  let running = false

  return {
    async event(input) {
      if (input.event.type !== "session.idle") return
      if (running) return

      running = true
      const sessionID = input.event.properties.sessionID

      try {
        await prompt(
          client,
          sessionID,
          [
            "Verify your implementation against institutional knowledge.",
            "1. Re-read `docs/solutions/patterns/critical-patterns.md` — confirm no pattern was violated.",
            "2. If you modified modules not covered by the task preparation learnings, search `docs/solutions/` for those modules now.",
            "3. Check `~/shared-knowledge/shared/recent-alerts.md` for any recent warnings.",
            "If you find violations, fix them before proceeding.",
          ].join("\n"),
        )
        await prompt(client, sessionID, "Run `composer check` and fix any failures")
        await prompt(
          client,
          sessionID,
          "If any Vue/Blade/CSS files were changed in this session, use agent-browser to validate the UI renders correctly. Otherwise skip this step.",
        )
        await command(client, sessionID, "workflows-review")
        await prompt(client, sessionID, "Implement all recommendations from the review above")
        await prompt(client, sessionID, "Run `composer check` and fix any failures")
        await command(client, sessionID, "workflows-compound")
        await command(client, sessionID, "finalize")
        await prompt(client, sessionID, "Push all changes to git. Do not skip any git hooks.")
      } finally {
        running = false
      }
    },
  }
}
